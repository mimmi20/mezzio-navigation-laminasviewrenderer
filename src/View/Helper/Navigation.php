<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace Mezzio\Navigation\LaminasView\View\Helper;

use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\HelperInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\HelperTrait;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap;

/**
 * Proxy helper for retrieving navigational helpers and forwarding calls
 *
 * @method Breadcrumbs breadcrumbs(ContainerInterface|string|null $container = null)
 * @method Links       links(ContainerInterface|string|null $container = null)
 * @method Menu        menu(ContainerInterface|string|null $container = null)
 * @method Sitemap     sitemap(ContainerInterface|string|null $container = null)
 */
final class Navigation extends AbstractHtmlElement implements HelperInterface
{
    use HelperTrait {
        __call as parentCall;
    }

    /**
     * Default proxy to use in {@link render()}
     *
     * @var string
     */
    private $defaultProxy = 'menu';

    /**
     * Indicates whether or not a given helper has been injected
     *
     * @var array
     */
    private $injected = [];

    /**
     * Whether ACL should be injected when proxying
     *
     * @var bool
     */
    private $injectAuthorization = true;

    /**
     * Whether translator should be injected when proxying
     *
     * @var bool
     */
    private $injectTranslator = true;

    /** @var HelperPluginManager|null */
    private $pluginManager;

    /**
     * Magic overload: Proxy to other navigation helpers or the container
     *
     * Examples of usage from a view script or layout:
     * <code>
     * // proxy to Menu helper and render container:
     * echo $this->navigation()->menu();
     *
     * // proxy to Breadcrumbs helper and set indentation:
     * $this->navigation()->breadcrumbs()->setIndent(8);
     *
     * // proxy to container and find all pages with 'blog' route:
     * $blogPages = $this->navigation()->findAllByRoute('blog');
     * </code>
     *
     * @param string $method    helper name or method name in container
     * @param array  $arguments [optional] arguments to pass
     *
     * @throws \Laminas\View\Exception\ExceptionInterface         if proxying to a helper, and the
     *                                                            helper is not an instance of the
     *                                                            interface specified in
     *                                                            {@link findHelper()}
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface    if method does not exist in container
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return mixed returns what the proxied call returns
     */
    public function __call(string $method, array $arguments = [])
    {
        // check if call should proxy to another helper
        $helper = $this->findHelper($method, false);

        if ($helper) {
            return call_user_func_array($helper, $arguments);
        }

        // default behaviour: proxy call to container
        return $this->parentCall($method, $arguments);
    }

    /**
     * Renders helper
     *
     * @param ContainerInterface|null $container
     *
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function render(?ContainerInterface $container = null): string
    {
        try {
            $helper = $this->findHelper($this->getDefaultProxy());
        } catch (Exception\RuntimeException $e) {
            $this->logger->err($e);

            return '';
        }

        if (null === $helper) {
            return '';
        }

        return $helper->render($container);
    }

    /**
     * Returns the helper matching $proxy
     *
     * The helper must implement the interface
     * {@link \Mezzio\Navigation\LaminasView\View\Helper\Navigation\HelperInterface}.
     *
     * @param string $proxy  helper name
     * @param bool   $strict [optional] whether exceptions should be
     *                       thrown if something goes
     *                       wrong. Default is true.
     *
     * @throws Exception\RuntimeException                            if $strict is true and helper cannot be found
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return HelperInterface|null helper instance
     */
    public function findHelper(string $proxy, bool $strict = true): ?HelperInterface
    {
        if (null === $this->pluginManager) {
            if ($strict) {
                throw new Exception\RuntimeException(
                    sprintf('Failed to find plugin for %s, no PluginManager set', $proxy)
                );
            }

            return null;
        }

        if (!$this->pluginManager->has($proxy)) {
            if ($strict) {
                throw new Exception\RuntimeException(
                    sprintf('Failed to find plugin for %s', $proxy)
                );
            }

            return null;
        }

        try {
            $helper = $this->pluginManager->get($proxy);
        } catch (ServiceNotFoundException | InvalidServiceException $e) {
            if ($strict) {
                throw new Exception\RuntimeException(
                    sprintf('Failed to load plugin for %s', $proxy)
                );
            }

            return null;
        }

        $container = $this->getContainer();
        $hash      = spl_object_hash($container) . spl_object_hash($helper);

        if (!isset($this->injected[$hash])) {
            $helper->setContainer($container);
            $this->inject($helper);
            $this->injected[$hash] = true;
        }

        return $helper;
    }

    /**
     * Injects container, ACL, and translator to the given $helper if this
     * helper is configured to do so
     *
     * @param HelperInterface $helper helper instance
     *
     * @throws \Laminas\View\Exception\ExceptionInterface
     *
     * @return void
     */
    private function inject(HelperInterface $helper): void
    {
        if ($this->getInjectAuthorization()) {
            if (!$helper->hasAuthorization()) {
                $helper->setAuthorization($this->getAuthorization());
            }

            $role = $this->getRole();

            if (!$helper->hasRole() && null !== $role) {
                $helper->setRole($role);
            }
        }

        if (!$this->getInjectTranslator() || $helper->hasTranslator()) {
            return;
        }

        $helper->setTranslator(
            $this->getTranslator(),
            $this->getTranslatorTextDomain()
        );
    }

    /**
     * Sets the default proxy to use in {@link render()}
     *
     * @param string $proxy default proxy
     *
     * @return void
     */
    public function setDefaultProxy(string $proxy): void
    {
        $this->defaultProxy = $proxy;
    }

    /**
     * Returns the default proxy to use in {@link render()}
     *
     * @return string
     */
    public function getDefaultProxy(): string
    {
        return $this->defaultProxy;
    }

    /**
     * Sets whether Authorization should be injected when proxying
     *
     * @param bool $injectAuthorization
     *
     * @return void
     */
    public function setInjectAuthorization(bool $injectAuthorization = true): void
    {
        $this->injectAuthorization = $injectAuthorization;
    }

    /**
     * Returns whether Authorization should be injected when proxying
     *
     * @return bool
     */
    public function getInjectAuthorization(): bool
    {
        return $this->injectAuthorization;
    }

    /**
     * Sets whether translator should be injected when proxying
     *
     * @param bool $injectTranslator
     *
     * @return void
     */
    public function setInjectTranslator(bool $injectTranslator = true): void
    {
        $this->injectTranslator = $injectTranslator;
    }

    /**
     * Returns whether translator should be injected when proxying
     *
     * @return bool
     */
    public function getInjectTranslator(): bool
    {
        return $this->injectTranslator;
    }

    /**
     * Set manager for retrieving navigation helpers
     *
     * @param HelperPluginManager $pluginManager
     *
     * @return void
     */
    public function setPluginManager(HelperPluginManager $pluginManager): void
    {
        $renderer = $this->getView();

        if ($renderer) {
            $pluginManager->setRenderer($renderer);
        }

        $this->pluginManager = $pluginManager;
    }

    /**
     * Set the View object
     *
     * @param Renderer $view
     *
     * @return self
     */
    public function setView(Renderer $view)
    {
        parent::setView($view);

        if ($this->pluginManager) {
            $this->pluginManager->setRenderer($view);
        }

        return $this;
    }
}
