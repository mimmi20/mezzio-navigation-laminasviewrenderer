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

use Laminas\View\Exception;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Mezzio\Navigation\AbstractContainer;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\AbstractHelper;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\HelperInterface;

/**
 * Proxy helper for retrieving navigational helpers and forwarding calls
 *
 * @method \Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs breadcrumbs(AbstractContainer|string|null $container = null)
 * @method \Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links       links(AbstractContainer|string|null $container = null)
 * @method \Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu        menu(AbstractContainer|string|null $container = null)
 * @method \Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap     sitemap(AbstractContainer|string|null $container = null)
 */
final class Navigation extends AbstractHelper
{
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
    private $injectAcl = true;

    /**
     * Whether container should be injected when proxying
     *
     * @var bool
     */
    private $injectContainer = true;

    /**
     * Whether translator should be injected when proxying
     *
     * @var bool
     */
    private $injectTranslator = true;

    /** @var Navigation\PluginManager */
    private $plugins;

    /**
     * Helper entry point
     *
     * @param AbstractContainer|string|null $container container to operate on
     *
     * @return Navigation
     */
    public function __invoke($container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

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
     * @throws \Laminas\View\Exception\ExceptionInterface      if proxying to a helper, and the
     *                                                         helper is not an instance of the
     *                                                         interface specified in
     *                                                         {@link findHelper()}
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface if method does not exist in container
     *
     * @return mixed returns what the proxied call returns
     */
    public function __call($method, array $arguments = [])
    {
        // check if call should proxy to another helper
        $helper = $this->findHelper($method, false);

        if ($helper) {
            if (method_exists($helper, 'setServiceLocator') && $this->getServiceLocator()) {
                $helper->setServiceLocator($this->getServiceLocator());
            }

            return call_user_func_array($helper, $arguments);
        }

        // default behaviour: proxy call to container
        return parent::__call($method, $arguments);
    }

    /**
     * Renders helper
     *
     * @param AbstractContainer $container
     *
     * @throws Exception\RuntimeException
     *
     * @return string
     */
    public function render($container = null): string
    {
        return $this->findHelper($this->getDefaultProxy())->render($container);
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
     * @throws Exception\RuntimeException if $strict is true and helper cannot be found
     *
     * @return HelperInterface|null helper instance
     */
    public function findHelper(string $proxy, bool $strict = true): ?HelperInterface
    {
        if (!$this->plugins->has($proxy)) {
            if ($strict) {
                throw new Exception\RuntimeException(
                    sprintf('Failed to find plugin for %s', $proxy)
                );
            }

            return null;
        }

        $helper    = $this->plugins->get($proxy);
        $container = $this->getContainer();
        $hash      = spl_object_hash($container) . spl_object_hash($helper);

        if (!isset($this->injected[$hash])) {
            $helper->setContainer();
            $this->inject($helper);
            $this->injected[$hash] = true;
        } else {
            if ($this->getInjectContainer()) {
                $helper->setContainer($container);
            }
        }

        return $helper;
    }

    /**
     * Injects container, ACL, and translator to the given $helper if this
     * helper is configured to do so
     *
     * @param HelperInterface $helper helper instance
     *
     * @return void
     */
    private function inject(HelperInterface $helper): void
    {
        if ($this->getInjectContainer() && !$helper->hasContainer()) {
            $helper->setContainer($this->getContainer());
        }

        if ($this->getInjectAcl()) {
            if (!$helper->hasAcl()) {
                $helper->setAcl($this->getAcl());
            }

            if (!$helper->hasRole()) {
                $helper->setRole($this->getRole());
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
     * Sets whether container should be injected when proxying
     *
     * @param bool $injectContainer
     *
     * @return void
     */
    public function setInjectContainer(bool $injectContainer = true): void
    {
        $this->injectContainer = $injectContainer;
    }

    /**
     * Returns whether container should be injected when proxying
     *
     * @return bool
     */
    public function getInjectContainer(): bool
    {
        return $this->injectContainer;
    }

    /**
     * Sets whether ACL should be injected when proxying
     *
     * @param bool $injectAcl
     *
     * @return void
     */
    public function setInjectAcl(bool $injectAcl = true): void
    {
        $this->injectAcl = $injectAcl;
    }

    /**
     * Returns whether ACL should be injected when proxying
     *
     * @return bool
     */
    public function getInjectAcl(): bool
    {
        return $this->injectAcl;
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
     * @param Navigation\PluginManager $plugins
     *
     * @return void
     */
    public function setPluginManager(Navigation\PluginManager $plugins): void
    {
        $renderer = $this->getView();

        if ($renderer) {
            $plugins->setRenderer($renderer);
        }

        $this->plugins = $plugins;
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

        if ($view && $this->plugins) {
            $this->plugins->setRenderer($view);
        }

        return $this;
    }
}
