<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mezzio\Navigation\LaminasView\View\Helper;

use Laminas\Log\Logger;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\HelperTrait;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;

use function call_user_func_array;
use function spl_object_hash;
use function sprintf;

/**
 * Proxy helper for retrieving navigational helpers and forwarding calls
 *
 * @method Breadcrumbs breadcrumbs(ContainerInterface|string|null $container = null)
 * @method Links       links(ContainerInterface|string|null $container = null)
 * @method Menu        menu(ContainerInterface|string|null $container = null)
 * @method Sitemap     sitemap(ContainerInterface|string|null $container = null)
 */
final class Navigation extends AbstractHtmlElement implements ViewHelperInterface
{
    use HelperTrait {
        __call as parentCall;
    }

    /**
     * Default proxy to use in {@link render()}
     */
    private string $defaultProxy = 'menu';

    /**
     * Indicates whether or not a given helper has been injected
     *
     * @var array<string, bool>
     */
    private array $injected = [];

    /**
     * Whether ACL should be injected when proxying
     */
    private bool $injectAuthorization = true;

    private ?ViewHelperPluginManager $pluginManager = null;

    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        Logger $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;
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
     * @param string       $method    helper name or method name in container
     * @param array<mixed> $arguments [optional] arguments to pass
     *
     * @return mixed returns what the proxied call returns
     */
    public function __call(string $method, array $arguments = [])
    {
        // check if call should proxy to another helper
        try {
            $helper = $this->findHelperStrict($method);
        } catch (RuntimeException $e) {
            $this->logger->err($e);

            // default behaviour: proxy call to container
            return $this->parentCall($method, $arguments);
        }

        return call_user_func_array($helper, $arguments);
    }

    /**
     * Renders helper
     *
     * @param ContainerInterface|string|null $container
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function render($container = null): string
    {
        try {
            $helper = $this->findHelperStrict($this->getDefaultProxy());
        } catch (RuntimeException $e) {
            $this->logger->err($e);

            return '';
        }

        return $helper->render($container);
    }

    /**
     * Returns the helper matching $proxy
     *
     * The helper must implement the interface
     * {@link \Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface}.
     *
     * @param string $proxy  helper name
     * @param bool   $strict [optional] whether exceptions should be
     *                       thrown if something goes
     *                       wrong. Default is true.
     *
     * @return ViewHelperInterface|null helper instance
     *
     * @throws RuntimeException if $strict is true and helper cannot be found
     */
    public function findHelper(string $proxy, bool $strict = true): ?ViewHelperInterface
    {
        if ($strict) {
            return $this->findHelperStrict($proxy);
        }

        return $this->findHelperNonStrict($proxy);
    }

    /**
     * Sets the default proxy to use in {@link render()}
     *
     * @param string $proxy default proxy
     */
    public function setDefaultProxy(string $proxy): void
    {
        $this->defaultProxy = $proxy;
    }

    /**
     * Returns the default proxy to use in {@link render()}
     */
    public function getDefaultProxy(): string
    {
        return $this->defaultProxy;
    }

    /**
     * Sets whether Authorization should be injected when proxying
     */
    public function setInjectAuthorization(bool $injectAuthorization = true): void
    {
        $this->injectAuthorization = $injectAuthorization;
    }

    /**
     * Returns whether Authorization should be injected when proxying
     */
    public function getInjectAuthorization(): bool
    {
        return $this->injectAuthorization;
    }

    /**
     * Set manager for retrieving navigation helpers
     */
    public function setPluginManager(ViewHelperPluginManager $pluginManager): void
    {
        $renderer = $this->getView();

        if ($renderer) {
            $pluginManager->setRenderer($renderer);
        }

        $this->pluginManager = $pluginManager;
    }

    public function getPluginManager(): ?ViewHelperPluginManager
    {
        return $this->pluginManager;
    }

    /**
     * Set the View object
     */
    public function setView(Renderer $view): self
    {
        parent::setView($view);

        if ($this->pluginManager) {
            $this->pluginManager->setRenderer($view);
        }

        return $this;
    }

    /**
     * Returns the helper matching $proxy
     *
     * The helper must implement the interface
     * {@link \Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface}.
     *
     * @param string $proxy helper name
     *
     * @return ViewHelperInterface|null helper instance
     */
    private function findHelperNonStrict(string $proxy): ?ViewHelperInterface
    {
        if (null === $this->pluginManager) {
            return null;
        }

        if (!$this->pluginManager->has($proxy)) {
            return null;
        }

        try {
            $helper = $this->pluginManager->get($proxy);
        } catch (ServiceNotFoundException | InvalidServiceException $e) {
            $this->logger->debug($e);

            return null;
        }

        $this->prepareHelper($helper);

        return $helper;
    }

    /**
     * Returns the helper matching $proxy
     *
     * The helper must implement the interface
     * {@link \Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface}.
     *
     * @param string $proxy helper name
     *
     * @return ViewHelperInterface helper instance
     *
     * @throws RuntimeException if helper cannot be found
     */
    private function findHelperStrict(string $proxy): ViewHelperInterface
    {
        if (null === $this->pluginManager) {
            throw new RuntimeException(
                sprintf('Failed to find plugin for %s, no PluginManager set', $proxy)
            );
        }

        if (!$this->pluginManager->has($proxy)) {
            throw new RuntimeException(
                sprintf('Failed to find plugin for %s', $proxy)
            );
        }

        try {
            $helper = $this->pluginManager->get($proxy);
        } catch (ServiceNotFoundException | InvalidServiceException $e) {
            throw new RuntimeException(
                sprintf('Failed to load plugin for %s', $proxy),
                0,
                $e
            );
        }

        $this->prepareHelper($helper);

        return $helper;
    }

    private function prepareHelper(ViewHelperInterface $helper): void
    {
        $container = $this->getContainer();
        $hash      = spl_object_hash($container) . spl_object_hash($helper);

        if (!isset($this->injected[$hash])) {
            $helper->setContainer();

            $this->inject($helper);

            $this->injected[$hash] = true;
        } elseif ($this->getInjectContainer()) {
            $helper->setContainer($container);
        }
    }

    /**
     * Injects container, ACL, and translator to the given $helper if this
     * helper is configured to do so
     *
     * @param ViewHelperInterface $helper helper instance
     */
    private function inject(ViewHelperInterface $helper): void
    {
        if ($this->getInjectContainer() && !$helper->hasContainer()) {
            $helper->setContainer($this->getContainer());
        }

        if ($this->getInjectAuthorization() && !$helper->hasAuthorization()) {
            $helper->setAuthorization($this->getAuthorization());
        }

        $role = $this->getRole();

        if ($helper->hasRole() || null === $role) {
            return;
        }

        $helper->setRole($role);
    }
}
