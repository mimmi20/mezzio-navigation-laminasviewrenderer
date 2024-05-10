<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\Mezzio\Navigation\LaminasView\View\Helper;

use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\HelperTrait;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Log\LoggerInterface;

use function assert;
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
     * Indicates whether a given helper has been injected
     *
     * @var array<string, bool>
     */
    private array $injected = [];

    /**
     * Whether ACL should be injected when proxying
     */
    private bool $injectAuthorization = true;

    /** @var ViewHelperPluginManager<HelperInterface>|null */
    private ViewHelperPluginManager | null $pluginManager = null;

    /** @throws void */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        LoggerInterface $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
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
     * @param string                                               $method    helper name or method name in container
     * @param array<ContainerInterface<PageInterface>|string|null> $arguments [optional] arguments to pass
     *
     * @return mixed returns what the proxied call returns
     *
     * @throws InvalidArgumentException
     */
    public function __call(string $method, array $arguments = []): mixed
    {
        // check if call should proxy to another helper
        try {
            $helper = $this->findHelperStrict($method);
        } catch (RuntimeException $e) {
            $this->logger->error($e);

            // default behaviour: proxy call to container
            return $this->parentCall($method, $arguments);
        }

        return $helper(...$arguments);
    }

    /**
     * Renders helper
     *
     * @param ContainerInterface<PageInterface>|string|null $container
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function render(ContainerInterface | string | null $container = null): string
    {
        try {
            $helper = $this->findHelperStrict($this->getDefaultProxy());
        } catch (RuntimeException $e) {
            $this->logger->error($e);

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
     *
     * @api
     */
    public function findHelper(string $proxy, bool $strict = true): ViewHelperInterface | null
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
     *
     * @throws void
     *
     * @api
     */
    public function setDefaultProxy(string $proxy): void
    {
        $this->defaultProxy = $proxy;
    }

    /**
     * Returns the default proxy to use in {@link render()}
     *
     * @throws void
     *
     * @api
     */
    public function getDefaultProxy(): string
    {
        return $this->defaultProxy;
    }

    /**
     * Sets whether Authorization should be injected when proxying
     *
     * @throws void
     *
     * @api
     */
    public function setInjectAuthorization(bool $injectAuthorization = true): void
    {
        $this->injectAuthorization = $injectAuthorization;
    }

    /**
     * Returns whether Authorization should be injected when proxying
     *
     * @throws void
     */
    public function getInjectAuthorization(): bool
    {
        return $this->injectAuthorization;
    }

    /**
     * Set manager for retrieving navigation helpers
     *
     * @param ViewHelperPluginManager<HelperInterface> $pluginManager
     *
     * @throws void
     */
    public function setPluginManager(ViewHelperPluginManager $pluginManager): void
    {
        $renderer = $this->getView();

        if ($renderer) {
            $pluginManager->setRenderer($renderer);
        }

        $this->pluginManager = $pluginManager;
    }

    /**
     * @return ViewHelperPluginManager<HelperInterface>|null
     *
     * @throws void
     *
     * @api
     */
    public function getPluginManager(): ViewHelperPluginManager | null
    {
        return $this->pluginManager;
    }

    /**
     * Set the View object
     *
     * @throws void
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
     * {@link \Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface}.
     *
     * @param string $proxy helper name
     *
     * @return ViewHelperInterface|null helper instance
     *
     * @throws void
     */
    private function findHelperNonStrict(string $proxy): ViewHelperInterface | null
    {
        if ($this->pluginManager === null) {
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

        assert($helper instanceof ViewHelperInterface);

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
        if ($this->pluginManager === null) {
            throw new RuntimeException(
                sprintf('Failed to find plugin for %s, no PluginManager set', $proxy),
            );
        }

        if (!$this->pluginManager->has($proxy)) {
            throw new RuntimeException(
                sprintf('Failed to find plugin for %s', $proxy),
            );
        }

        try {
            $helper = $this->pluginManager->get($proxy);
        } catch (ServiceNotFoundException | InvalidServiceException $e) {
            throw new RuntimeException(
                sprintf('Failed to load plugin for %s', $proxy),
                0,
                $e,
            );
        }

        assert($helper instanceof ViewHelperInterface);

        $this->prepareHelper($helper);

        return $helper;
    }

    /** @throws void */
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
     *
     * @throws void
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

        if ($helper->hasRole() || $role === null) {
            return;
        }

        $helper->setRole($role);
    }
}
