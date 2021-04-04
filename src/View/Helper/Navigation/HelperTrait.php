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

namespace Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\View\Exception;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation;
use Mezzio\Navigation\Helper\AcceptHelperInterface;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\FindActiveInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Helper\PluginManager as HelperPluginManager;
use Mezzio\Navigation\Page\PageInterface;
use Psr\Container\ContainerExceptionInterface;

use function assert;
use function call_user_func_array;
use function get_class;
use function is_int;
use function sprintf;
use function str_repeat;

/**
 * Base class for navigational helpers.
 *
 * Duck-types against Laminas\I18n\Translator\TranslatorAwareInterface.
 */
trait HelperTrait
{
    /**
     * ContainerInterface to operate on by default
     */
    private ?\Mezzio\Navigation\ContainerInterface $container = null;

    private ?string $navigation = null;

    private Logger $logger;

    private HtmlifyInterface $htmlify;

    private ContainerParserInterface $containerParser;

    /**
     * The minimum depth a page must have to be included when rendering
     */
    private ?int $minDepth = null;

    /**
     * The maximum depth a page can have to be included when rendering
     */
    private ?int $maxDepth = null;

    /**
     * Indentation string
     */
    private string $indent = '';

    /**
     * Authorization to use when iterating pages
     */
    private ?AuthorizationInterface $authorization = null;

    /**
     * Whether invisible items should be rendered by this helper
     */
    private bool $renderInvisible = false;

    /**
     * Authorization role to use when iterating pages
     */
    private ?string $role = null;

    private ContainerInterface $serviceLocator;

    /**
     * Whether container should be injected when proxying
     */
    private bool $injectContainer = true;

    /**
     * Whether Authorization should be used for filtering out pages
     */
    private bool $useAuthorization = true;

    /**
     * Default Authorization role to use when iterating pages if not explicitly set in the
     * instance by calling {@link setRole()}
     */
    private static ?string $defaultRole = null;

    private static ?AuthorizationInterface $defaultAuthorization = null;

    /**
     * Helper entry point
     *
     * @param Navigation\ContainerInterface|string|null $container container to operate on
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke($container = null): self
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Magic overload: Proxy calls to the navigation container
     *
     * @param string       $method    method name in container
     * @param array<mixed> $arguments rguments to pass
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
     */
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (Exception\ExceptionInterface $e) {
            $this->logger->err($e);

            return '';
        }
    }

    /**
     * Sets navigation container the helper operates on by default
     *
     * Implements {@link ViewHelperInterface::setContainer()}.
     *
     * @param Navigation\ContainerInterface|string|null $container default is null, meaning container will be reset
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setContainer($container = null): self
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
     * @return Navigation\ContainerInterface navigation container
     */
    public function getContainer(): Navigation\ContainerInterface
    {
        if (null === $this->container) {
            $this->container = new Navigation\Navigation();
        }

        return $this->container;
    }

    /**
     * Sets whether container should be injected when proxying
     */
    public function setInjectContainer(bool $injectContainer = true): self
    {
        $this->injectContainer = $injectContainer;

        return $this;
    }

    /**
     * Returns whether container should be injected when proxying
     */
    public function getInjectContainer(): bool
    {
        return $this->injectContainer;
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
     * @return array<string, int|PageInterface|null> an associative array with the values 'depth' and 'page',
     *                       or an empty array if not found
     *
     * @throws Exception\InvalidArgumentException
     */
    public function findActive($container, ?int $minDepth = null, ?int $maxDepth = -1): array
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
            assert(
                $helperPluginManager instanceof PluginManagerInterface,
                sprintf(
                    '$helperPluginManager should be an Instance of %s, but was %s',
                    HelperPluginManager::class,
                    get_class($helperPluginManager)
                )
            );

            $findActiveHelper = $helperPluginManager->build(
                FindActiveInterface::class,
                [
                    'authorization' => $this->getUseAuthorization() ? $this->getAuthorization() : null,
                    'renderInvisible' => $this->getRenderInvisible(),
                    'role' => $this->getRole(),
                ]
            );
        } catch (ContainerExceptionInterface $e) {
            $this->logger->err($e);

            return [];
        }

        assert($findActiveHelper instanceof FindActiveInterface);

        return $findActiveHelper->find($container, $minDepth, $maxDepth);
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
    public function accept(PageInterface $page, bool $recursive = true): bool
    {
        try {
            $helperPluginManager = $this->serviceLocator->get(HelperPluginManager::class);
            assert(
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
        } catch (ContainerExceptionInterface $e) {
            $this->logger->err($e);

            return false;
        }

        assert($acceptHelper instanceof AcceptHelperInterface);

        return $acceptHelper->accept($page, $recursive);
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
     */
    public function setAuthorization(?AuthorizationInterface $authorization = null): self
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
    public function getAuthorization(): ?AuthorizationInterface
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
     */
    public function hasAuthorization(): bool
    {
        return $this->authorization instanceof AuthorizationInterface
            || static::$defaultAuthorization instanceof AuthorizationInterface;
    }

    /**
     * Checks if the helper has a container
     *
     * Implements {@link ViewHelperInterface::hasContainer()}.
     */
    public function hasContainer(): bool
    {
        return null !== $this->container;
    }

    /**
     * Set the indentation string for using in {@link render()}, optionally a
     * number of spaces to indent with
     *
     * @param int|string $indent
     */
    public function setIndent($indent): self
    {
        $this->indent = $this->getWhitespace($indent);

        return $this;
    }

    /**
     * Returns indentation
     */
    public function getIndent(): string
    {
        return $this->indent;
    }

    /**
     * Sets the maximum depth a page can have to be included when rendering
     *
     * @param int $maxDepth default is null, which sets no maximum depth
     */
    public function setMaxDepth(int $maxDepth): self
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Returns maximum depth a page can have to be included when rendering
     */
    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }

    /**
     * Sets the minimum depth a page must have to be included when rendering
     *
     * @param int $minDepth default is null, which sets no minimum depth
     */
    public function setMinDepth(int $minDepth): self
    {
        $this->minDepth = $minDepth;

        return $this;
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     */
    public function getMinDepth(): ?int
    {
        if (!is_int($this->minDepth) || 0 > $this->minDepth) {
            return 0;
        }

        return $this->minDepth;
    }

    /**
     * Render invisible items?
     */
    public function setRenderInvisible(bool $renderInvisible = true): self
    {
        $this->renderInvisible = $renderInvisible;

        return $this;
    }

    /**
     * Return renderInvisible flag
     */
    public function getRenderInvisible(): bool
    {
        return $this->renderInvisible;
    }

    /**
     * Sets Authorization role(s) to use when iterating pages
     *
     * Implements {@link ViewHelperInterface::setRole()}.
     *
     * @param string $role [optional] role to set. Expects a string or null. Default is null, which will set no role.
     */
    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Returns Authorization role to use when iterating pages, or null if it isn't set
     * using {@link setRole()} or {@link setDefaultRole()}
     *
     * Implements {@link ViewHelperInterface::getRole()}.
     */
    public function getRole(): ?string
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
     */
    public function hasRole(): bool
    {
        return null !== $this->role
            || null !== static::$defaultRole;
    }

    /**
     * Sets whether Authorization should be used
     * Implements {@link ViewHelperInterface::setUseAuthorization()}.
     */
    public function setUseAuthorization(bool $useAuthorization = true): self
    {
        $this->useAuthorization = $useAuthorization;

        return $this;
    }

    /**
     * Returns whether Authorization should be used
     * Implements {@link ViewHelperInterface::getUseAuthorization()}.
     */
    public function getUseAuthorization(): bool
    {
        return $this->useAuthorization;
    }

    public function getServiceLocator(): ContainerInterface
    {
        return $this->serviceLocator;
    }

    // Static methods:

    /**
     * Sets default Authorization to use if another Authorization is not explicitly set
     *
     * @param AuthorizationInterface|null $authorization [optional] Authorization object. Default is null, which
     *                                                   sets no Authorization object.
     */
    public static function setDefaultAuthorization(?AuthorizationInterface $authorization = null): void
    {
        static::$defaultAuthorization = $authorization;
    }

    /**
     * Sets default Authorization role(s) to use when iterating pages if not explicitly
     * set later with {@link setRole()}
     *
     * @param string|null $role [optional] role to set. Expects null or string. Default is null, which
     *                          sets no default role.
     */
    public static function setDefaultRole(?string $role = null): void
    {
        static::$defaultRole = $role;
    }

    // Util methods:

    /**
     * Retrieve whitespace representation of $indent
     *
     * @param int|string $indent
     */
    private function getWhitespace($indent): string
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }

        return (string) $indent;
    }
}
