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
namespace Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\View\Exception\ExceptionInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation;
use Mezzio\Navigation\LaminasView\Helper\AcceptHelperInterface;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\FindActiveInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;
use Mezzio\Navigation\Page\PageInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * Base class for navigational helpers.
 *
 * Duck-types against Laminas\I18n\Translator\TranslatorAwareInterface.
 */
trait HelperTrait
{
    /**
     * ContainerInterface to operate on by default
     *
     * @var \Mezzio\Navigation\ContainerInterface|null
     */
    private $container;

    /** @var string|null */
    private $navigation;

    /** @var \Laminas\Log\Logger */
    private $logger;

    /** @var HtmlifyInterface */
    private $htmlify;

    /** @var ContainerParserInterface */
    private $containerParser;

    /**
     * The minimum depth a page must have to be included when rendering
     *
     * @var int|null
     */
    private $minDepth;

    /**
     * The maximum depth a page can have to be included when rendering
     *
     * @var int|null
     */
    private $maxDepth;

    /**
     * Indentation string
     *
     * @var string
     */
    private $indent = '';

    /**
     * Authorization to use when iterating pages
     *
     * @var \Mezzio\GenericAuthorization\AuthorizationInterface|null
     */
    private $authorization;

    /**
     * Whether invisible items should be rendered by this helper
     *
     * @var bool
     */
    private $renderInvisible = false;

    /**
     * Authorization role to use when iterating pages
     *
     * @var string|null
     */
    private $role;

    /** @var ContainerInterface */
    private $serviceLocator;

    /**
     * Whether container should be injected when proxying
     *
     * @var bool
     */
    private $injectContainer = true;

    /**
     * Whether Authorization should be used for filtering out pages
     *
     * @var bool
     */
    private $useAuthorization = true;

    /**
     * Default Authorization role to use when iterating pages if not explicitly set in the
     * instance by calling {@link setRole()}
     *
     * @var string|null
     */
    private static $defaultRole;

    /** @var AuthorizationInterface|null */
    private static $defaultAuthorization;

    /**
     * Helper entry point
     *
     * @param Navigation\ContainerInterface|string|null $container container to operate on
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return self
     */
    final public function __invoke($container = null): self
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Sets navigation container the helper operates on by default
     *
     * Implements {@link ViewHelperInterface::setContainer()}.
     *
     * @param Navigation\ContainerInterface|string|null $container default is null, meaning container will be reset
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return self
     */
    final public function setContainer($container = null): self
    {
        $this->container = $this->containerParser->parseContainer($container);

        return $this;
    }

    /**
     * Returns the navigation container helper operates on by default
     *
     * Implements {@link ViewHelperInterface::getContainer()}.
     *
     * If no container is set, a new container will be instantiated and
     * stored in the helper.
     *
     *@throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return Navigation\ContainerInterface navigation container
     */
    final public function getContainer(): Navigation\ContainerInterface
    {
        if (null === $this->container) {
            $this->container = new Navigation\Navigation();
        }

        return $this->container;
    }

    /**
     * Sets whether container should be injected when proxying
     *
     * @param bool $injectContainer
     *
     * @return self
     */
    public function setInjectContainer(bool $injectContainer = true): self
    {
        $this->injectContainer = $injectContainer;

        return $this;
    }

    /**
     * Returns whether container should be injected when proxying
     *
     * @return bool
     */
    public function getInjectContainer()
    {
        return $this->injectContainer;
    }

    /**
     * Magic overload: Proxy calls to the navigation container
     *
     * @param string $method    method name in container
     * @param array  $arguments rguments to pass
     *
     * @throws Navigation\Exception\ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        return call_user_func_array(
            [$this->getContainer(), $method],
            $arguments
        );
    }

    /**
     * Magic overload: Proxy to {@link render()}.
     *
     * This method will trigger an E_USER_ERROR if rendering the helper causes
     * an exception to be thrown.
     *
     * Implements {@link ViewHelperInterface::__toString()}.
     *
     *@throws \Laminas\Validator\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    final public function __toString(): string
    {
        try {
            return $this->render();
        } catch (ExceptionInterface $e) {
            $this->logger->err($e);

            return '';
        }
    }

    /**
     * Finds the deepest active page in the given container
     *
     * @param Navigation\ContainerInterface|string|null $container to search
     * @param int|null                                  $minDepth  [optional] minimum depth
     *                                                             required for page to be
     *                                                             valid. Default is to use
     *                                                             {@link getMinDepth()}. A
     *                                                             null value means no minimum
     *                                                             depth required.
     * @param int|null                                  $maxDepth  [optional] maximum depth
     *                                                             a page can have to be
     *                                                             valid. Default is to use
     *                                                             {@link getMaxDepth()}. A
     *                                                             null value means no maximum
     *                                                             depth required.
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return array an associative array with the values 'depth' and 'page',
     *               or an empty array if not found
     */
    final public function findActive($container, ?int $minDepth = null, ?int $maxDepth = -1): array
    {
        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        if (null === $minDepth) {
            $minDepth = $this->getMinDepth();
        }

        if ((!is_int($maxDepth) || 0 > $maxDepth) && null !== $maxDepth) {
            $maxDepth = $this->getMaxDepth();
        }

        try {
            $helperPluginManager = $this->serviceLocator->get(HelperPluginManager::class);
            \assert(
                $helperPluginManager instanceof PluginManagerInterface,
                sprintf(
                    '$helperPluginManager should be an Instance of %s, but was %s',
                    HelperPluginManager::class,
                    get_class($helperPluginManager)
                )
            );

            $acceptHelper = $helperPluginManager->build(
                FindActiveInterface::class,
                [
                    'authorization' => $this->getUseAuthorization() ? $this->getAuthorization() : null,
                    'renderInvisible' => $this->getRenderInvisible(),
                    'role' => $this->getRole(),
                ]
            );
            \assert($acceptHelper instanceof FindActiveInterface);

            return $acceptHelper->find($container, $minDepth, $maxDepth);
        } catch (ContainerExceptionInterface $e) {
            $this->logger->err($e);

            return [];
        }
    }

    // Iterator filter methods:

    /**
     * Determines whether a page should be accepted when iterating
     *
     * Rules:
     * - If a page is not visible it is not accepted, unless RenderInvisible has
     *   been set to true
     * - If $useAuthorization is true (default is true):
     *      - Page is accepted if Authorization returns true, otherwise false
     * - If page is accepted and $recursive is true, the page
     *   will not be accepted if it is the descendant of a non-accepted page
     *
     * @param PageInterface $page      page to check
     * @param bool          $recursive [optional] if true, page will not be
     *                                 accepted if it is the descendant of
     *                                 a page that is not accepted. Default
     *                                 is true
     *
     * @return bool Whether page should be accepted
     */
    final public function accept(PageInterface $page, bool $recursive = true): bool
    {
        try {
            $helperPluginManager = $this->serviceLocator->get(HelperPluginManager::class);
            \assert(
                $helperPluginManager instanceof PluginManagerInterface,
                sprintf(
                    '$helperPluginManager should be an Instance of %s, but was %s',
                    HelperPluginManager::class,
                    get_class($helperPluginManager)
                )
            );

            $acceptHelper = $helperPluginManager->build(
                AcceptHelperInterface::class,
                [
                    'authorization' => $this->getUseAuthorization() ? $this->getAuthorization() : null,
                    'renderInvisible' => $this->getRenderInvisible(),
                    'role' => $this->getRole(),
                ]
            );
            \assert($acceptHelper instanceof AcceptHelperInterface);

            return $acceptHelper->accept($page, $recursive);
        } catch (ContainerExceptionInterface $e) {
            $this->logger->err($e);

            return false;
        }
    }

    // Util methods:

    /**
     * Retrieve whitespace representation of $indent
     *
     * @param int|string $indent
     *
     * @return string
     */
    private function getWhitespace($indent): string
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }

        return (string) $indent;
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page
     *
     * @param PageInterface $page page to generate HTML for
     *
     * @return string HTML string (<a href="…">Label</a>)
     */
    public function htmlify(PageInterface $page): string
    {
        return $this->htmlify->toHtml(static::class, $page);
    }

    /**
     * Sets AuthorizationInterface to use when iterating pages
     * Implements {@link ViewHelperInterface::setAuthorization()}.
     *
     * @param AuthorizationInterface|null $authorization AuthorizationInterface object
     *
     * @return self
     */
    final public function setAuthorization(?AuthorizationInterface $authorization = null): self
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * Returns AuthorizationInterface or null if it isn't set using {@link setAuthorization()} or
     * {@link setDefaultAuthorization()}
     *
     * Implements {@link ViewHelperInterface::getAuthorization()}.
     *
     * @return AuthorizationInterface|null AuthorizationInterface object or null
     */
    final public function getAuthorization(): ?AuthorizationInterface
    {
        if (null === $this->authorization && null !== static::$defaultAuthorization) {
            return static::$defaultAuthorization;
        }

        return $this->authorization;
    }

    /**
     * Checks if the helper has an Authorization instance
     *
     * Implements {@link ViewHelperInterface::hasAuthorization()}.
     *
     * @return bool
     */
    final public function hasAuthorization(): bool
    {
        return $this->authorization instanceof AuthorizationInterface
            || static::$defaultAuthorization instanceof AuthorizationInterface;
    }

    /**
     * Checks if the helper has a container
     *
     * Implements {@link ViewHelperInterface::hasContainer()}.
     *
     * @return bool
     */
    final public function hasContainer(): bool
    {
        return null !== $this->container;
    }

    /**
     * Set the indentation string for using in {@link render()}, optionally a
     * number of spaces to indent with
     *
     * @param int|string $indent
     *
     * @return self
     */
    final public function setIndent($indent): self
    {
        $this->indent = $this->getWhitespace($indent);

        return $this;
    }

    /**
     * Returns indentation
     *
     * @return string
     */
    final public function getIndent(): string
    {
        return $this->indent;
    }

    /**
     * Sets the maximum depth a page can have to be included when rendering
     *
     * @param int $maxDepth default is null, which sets no maximum depth
     *
     * @return self
     */
    final public function setMaxDepth(int $maxDepth): self
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Returns maximum depth a page can have to be included when rendering
     *
     * @return int|null
     */
    final public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }

    /**
     * Sets the minimum depth a page must have to be included when rendering
     *
     * @param int $minDepth default is null, which sets no minimum depth
     *
     * @return self
     */
    final public function setMinDepth(int $minDepth): self
    {
        $this->minDepth = $minDepth;

        return $this;
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @return int|null
     */
    final public function getMinDepth(): ?int
    {
        if (!is_int($this->minDepth) || 0 > $this->minDepth) {
            return 0;
        }

        return $this->minDepth;
    }

    /**
     * Render invisible items?
     *
     * @param bool $renderInvisible
     *
     * @return self
     */
    final public function setRenderInvisible(bool $renderInvisible = true): self
    {
        $this->renderInvisible = $renderInvisible;

        return $this;
    }

    /**
     * Return renderInvisible flag
     *
     * @return bool
     */
    final public function getRenderInvisible(): bool
    {
        return $this->renderInvisible;
    }

    /**
     * Sets Authorization role(s) to use when iterating pages
     *
     * Implements {@link ViewHelperInterface::setRole()}.
     *
     * @param string $role [optional] role to set. Expects a string or null. Default is null, which will set no role.
     *
     * @return self
     */
    final public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Returns Authorization role to use when iterating pages, or null if it isn't set
     * using {@link setRole()} or {@link setDefaultRole()}
     *
     * Implements {@link ViewHelperInterface::getRole()}.
     *
     * @return string|null
     */
    final public function getRole(): ?string
    {
        if (null === $this->role && null !== static::$defaultRole) {
            return static::$defaultRole;
        }

        return $this->role;
    }

    /**
     * Checks if the helper has an Authorization role
     *
     * Implements {@link ViewHelperInterface::hasRole()}.
     *
     * @return bool
     */
    final public function hasRole(): bool
    {
        return null !== $this->role
            || null !== static::$defaultRole;
    }

    /**
     * Sets whether Authorization should be used
     * Implements {@link ViewHelperInterface::setUseAuthorization()}.
     *
     * @param bool $useAuthorization
     *
     * @return self
     */
    final public function setUseAuthorization(bool $useAuthorization = true): self
    {
        $this->useAuthorization = $useAuthorization;

        return $this;
    }

    /**
     * Returns whether Authorization should be used
     * Implements {@link ViewHelperInterface::getUseAuthorization()}.
     *
     * @return bool
     */
    final public function getUseAuthorization(): bool
    {
        return $this->useAuthorization;
    }

    /**
     * @return \Interop\Container\ContainerInterface
     */
    final public function getServiceLocator(): ContainerInterface
    {
        return $this->serviceLocator;
    }

    // Static methods:

    /**
     * Sets default Authorization to use if another Authorization is not explicitly set
     *
     * @param \Mezzio\GenericAuthorization\AuthorizationInterface|null $authorization [optional] Authorization object. Default is null, which
     *                                                                                sets no Authorization object.
     *
     * @return void
     */
    final public static function setDefaultAuthorization(?AuthorizationInterface $authorization = null): void
    {
        static::$defaultAuthorization = $authorization;
    }

    /**
     * Sets default Authorization role(s) to use when iterating pages if not explicitly
     * set later with {@link setRole()}
     *
     * @param string|null $role [optional] role to set. Expects null or string. Default is null, which
     *                          sets no default role.
     *
     * @return void
     */
    final public static function setDefaultRole(?string $role = null): void
    {
        static::$defaultRole = $role;
    }
}
