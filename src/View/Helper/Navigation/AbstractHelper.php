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

namespace Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Laminas\I18n\Exception\RuntimeException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\DomainException;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHtmlElement;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;
use Stringable;

use function assert;
use function get_debug_type;
use function is_int;
use function sprintf;
use function str_repeat;

/**
 * Base class for navigational helpers.
 *
 * Duck-types against Laminas\I18n\Translator\TranslatorAwareInterface.
 */
abstract class AbstractHelper extends AbstractHtmlElement implements Stringable
{
    /**
     * ContainerInterface to operate on by default
     *
     * @var Navigation\ContainerInterface<PageInterface>|null
     */
    protected Navigation\ContainerInterface | null $container = null;
    protected string | null $navigation                       = null;

    /**
     * The minimum depth a page must have to be included when rendering
     */
    protected int | null $minDepth = null;

    /**
     * The maximum depth a page can have to be included when rendering
     */
    protected int | null $maxDepth = null;

    /**
     * Indentation string
     */
    protected string $indent = '';

    /**
     * Authorization to use when iterating pages
     */
    protected AuthorizationInterface | null $authorization = null;

    /**
     * Whether invisible items should be rendered by this helper
     */
    protected bool $renderInvisible = false;

    /**
     * Authorization role to use when iterating pages
     */
    protected string | null $role = null;

    /**
     * Whether container should be injected when proxying
     */
    protected bool $injectContainer = true;

    /**
     * Whether Authorization should be used for filtering out pages
     */
    protected bool $useAuthorization = true;

    /**
     * Default Authorization role to use when iterating pages if not explicitly set in the
     * instance by calling {@link setRole()}
     */
    protected static string | null $defaultRole                          = null;
    protected static AuthorizationInterface | null $defaultAuthorization = null;

    /** @throws void */
    public function __construct(
        protected ServiceLocatorInterface $serviceLocator,
        protected LoggerInterface $logger,
        protected HtmlifyInterface $htmlify,
        protected ContainerParserInterface $containerParser,
    ) {
    }

    /**
     * Helper entry point
     *
     * @param Navigation\ContainerInterface<PageInterface>|string|null $container container to operate on
     *
     * @throws InvalidArgumentException
     */
    public function __invoke(ContainerInterface | string | null $container = null): static
    {
        if ($container !== null) {
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
     * @throws void
     */
    public function __call(string $method, array $arguments = []): mixed
    {
        return $this->getContainer()->{$method}(...$arguments);
    }

    /**
     * Magic overload: Proxy to {@link render()}.
     *
     * This method will trigger an E_USER_ERROR if rendering the helper causes
     * an exception to be thrown.
     *
     * Implements {@link ViewHelperInterface::__toString()}.
     *
     * @throws void
     */
    #[Override]
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (Exception\ExceptionInterface | InvalidArgumentException | DomainException | RuntimeException $e) {
            $this->logger->error($e);

            return '';
        }
    }

    /**
     * Renders helper.
     *
     * Implements {@link ViewHelperInterface::render()}.
     *
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to render.
     *                                                  Default is null, which indicates
     *                                                  that the helper should render
     *                                                  the container returned by {@link getContainer()}.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    abstract public function render(ContainerInterface | string | null $container = null): string;

    /**
     * Sets navigation container the helper operates on by default
     *
     * Implements {@link ViewHelperInterface::setContainer()}.
     *
     * @param Navigation\ContainerInterface<PageInterface>|string|null $container default is null, meaning container will be reset
     *
     * @throws InvalidArgumentException
     *
     * @api
     */
    public function setContainer(ContainerInterface | string | null $container = null): static
    {
        $container = $this->containerParser->parseContainer($container);

        if ($container instanceof ContainerInterface || $container === null) {
            $this->container = $container;
        }

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
     * @return Navigation\ContainerInterface<PageInterface> navigation container
     *
     * @throws void
     *
     * @api
     */
    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $this->container = new Navigation\Navigation();
        }

        return $this->container;
    }

    /**
     * Sets whether container should be injected when proxying
     *
     * @throws void
     *
     * @api
     */
    public function setInjectContainer(bool $injectContainer = true): static
    {
        $this->injectContainer = $injectContainer;

        return $this;
    }

    /**
     * Returns whether container should be injected when proxying
     *
     * @throws void
     *
     * @api
     */
    public function getInjectContainer(): bool
    {
        return $this->injectContainer;
    }

    /**
     * Finds the deepest active page in the given container
     *
     * @param Navigation\ContainerInterface<PageInterface>|string|null $container to search
     * @param int|null                                                 $minDepth  [optional] minimum depth
     *                                                                            required for page to be
     *                                                                            valid. Default is to use
     *                                                                            {@link getMinDepth()}. A
     *                                                                            null value means no minimum
     *                                                                            depth required.
     * @param int|null                                                 $maxDepth  [optional] maximum depth
     *                                                                            a page can have to be
     *                                                                            valid. Default is to use
     *                                                                            {@link getMaxDepth()}. A
     *                                                                            null value means no maximum
     *                                                                            depth required.
     *
     * @return array<string, int|PageInterface|null> an associative array with the values 'depth' and 'page', or an empty array if not found
     * @phpstan-return array{page?: PageInterface|null, depth?: int|null}
     *
     * @throws InvalidArgumentException
     */
    public function findActive(
        ContainerInterface | string | null $container,
        int | null $minDepth = null,
        int | null $maxDepth = -1,
    ): array {
        $container = $this->containerParser->parseContainer($container);

        if ($container === null) {
            $container = $this->getContainer();
        }

        if ($minDepth === null) {
            $minDepth = $this->getMinDepth();
        }

        if ((!is_int($maxDepth) || 0 > $maxDepth) && $maxDepth !== null) {
            $maxDepth = $this->getMaxDepth();
        }

        try {
            $findActiveHelper = $this->serviceLocator->build(
                FindActiveInterface::class,
                [
                    'authorization' => $this->getUseAuthorization() ? $this->getAuthorization() : null,
                    'renderInvisible' => $this->getRenderInvisible(),
                    'role' => $this->getRole(),
                ],
            );
        } catch (ContainerExceptionInterface $e) {
            $this->logger->error($e);

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
     *
     * @throws void
     */
    public function accept(PageInterface $page, bool $recursive = true): bool
    {
        try {
            $acceptHelper = $this->serviceLocator->build(
                AcceptHelperInterface::class,
                [
                    'authorization' => $this->getUseAuthorization() ? $this->getAuthorization() : null,
                    'renderInvisible' => $this->getRenderInvisible(),
                    'role' => $this->getRole(),
                ],
            );
        } catch (ContainerExceptionInterface $e) {
            $this->logger->error($e);

            return false;
        }

        assert(
            $acceptHelper instanceof AcceptHelperInterface,
            sprintf(
                '$acceptHelper should be an Instance of %s, but was %s',
                AcceptHelperInterface::class,
                get_debug_type($acceptHelper),
            ),
        );

        return $acceptHelper->accept($page, $recursive);
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page
     *
     * @param PageInterface $page page to generate HTML for
     *
     * @return string HTML string (<a href="…">Label</a>)
     *
     * @throws Exception\InvalidArgumentException
     * @throws RuntimeException
     *
     * @api
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
     * @throws void
     *
     * @api
     */
    public function setAuthorization(AuthorizationInterface | null $authorization = null): static
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
     *
     * @throws void
     *
     * @api
     */
    public function getAuthorization(): AuthorizationInterface | null
    {
        if (
            !$this->authorization instanceof AuthorizationInterface
            && static::$defaultAuthorization !== null
        ) {
            return static::$defaultAuthorization;
        }

        return $this->authorization;
    }

    /**
     * Checks if the helper has an Authorization instance
     *
     * Implements {@link ViewHelperInterface::hasAuthorization()}.
     *
     * @throws void
     *
     * @api
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
     *
     * @throws void
     *
     * @api
     */
    public function hasContainer(): bool
    {
        return $this->container !== null;
    }

    /**
     * Set the indentation string for using in {@link render()}, optionally a
     * number of spaces to indent with
     *
     * @throws void
     *
     * @api
     */
    public function setIndent(int | string $indent): static
    {
        $this->indent = $this->getWhitespace($indent);

        return $this;
    }

    /**
     * Returns indentation
     *
     * @throws void
     *
     * @api
     */
    public function getIndent(): string
    {
        return $this->indent;
    }

    /**
     * Sets the maximum depth a page can have to be included when rendering
     *
     * @param int|null $maxDepth default is null, which sets no maximum depth
     *
     * @throws void
     *
     * @api
     */
    public function setMaxDepth(int | null $maxDepth): static
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Returns maximum depth a page can have to be included when rendering
     *
     * @throws void
     *
     * @api
     */
    public function getMaxDepth(): int | null
    {
        return $this->maxDepth;
    }

    /**
     * Sets the minimum depth a page must have to be included when rendering
     *
     * @param int|null $minDepth default is null, which sets no minimum depth
     *
     * @throws void
     *
     * @api
     */
    public function setMinDepth(int | null $minDepth): static
    {
        $this->minDepth = $minDepth;

        return $this;
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @throws void
     *
     * @api
     */
    public function getMinDepth(): int
    {
        if ($this->minDepth === null || $this->minDepth < 0) {
            return 0;
        }

        return $this->minDepth;
    }

    /**
     * Render invisible items?
     *
     * @throws void
     *
     * @api
     */
    public function setRenderInvisible(bool $renderInvisible = true): static
    {
        $this->renderInvisible = $renderInvisible;

        return $this;
    }

    /**
     * Return renderInvisible flag
     *
     * @throws void
     *
     * @api
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
     *
     * @throws void
     *
     * @api
     */
    public function setRole(string $role): static
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
     * @throws void
     *
     * @api
     */
    public function getRole(): string | null
    {
        if ($this->role === null && static::$defaultRole !== null) {
            return static::$defaultRole;
        }

        return $this->role;
    }

    /**
     * Checks if the helper has an Authorization role
     *
     * Implements {@link ViewHelperInterface::hasRole()}.
     *
     * @throws void
     *
     * @api
     */
    public function hasRole(): bool
    {
        return $this->role !== null
            || static::$defaultRole !== null;
    }

    /**
     * Sets whether Authorization should be used
     * Implements {@link ViewHelperInterface::setUseAuthorization()}.
     *
     * @throws void
     *
     * @api
     */
    public function setUseAuthorization(bool $useAuthorization = true): static
    {
        $this->useAuthorization = $useAuthorization;

        return $this;
    }

    /**
     * Returns whether Authorization should be used
     * Implements {@link ViewHelperInterface::getUseAuthorization()}.
     *
     * @throws void
     *
     * @api
     */
    public function getUseAuthorization(): bool
    {
        return $this->useAuthorization;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getServiceLocator(): ServiceLocatorInterface
    {
        return $this->serviceLocator;
    }

    // Static methods:

    /**
     * Sets default Authorization to use if another Authorization is not explicitly set
     *
     * @param AuthorizationInterface|null $authorization [optional] Authorization object. Default is null, which
     *                                                   sets no Authorization object.
     *
     * @throws void
     *
     * @api
     */
    public static function setDefaultAuthorization(AuthorizationInterface | null $authorization = null): void
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
     * @throws void
     *
     * @api
     */
    public static function setDefaultRole(string | null $role = null): void
    {
        static::$defaultRole = $role;
    }

    // Util methods:

    /**
     * Retrieve whitespace representation of $indent
     *
     * @throws void
     */
    protected function getWhitespace(int | string $indent): string
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }

        return $indent;
    }
}
