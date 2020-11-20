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
use Laminas\Log\Logger;
use Laminas\View\Exception;
use Laminas\View\Exception\ExceptionInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\Page\PageInterface;
use Psr\Container\ContainerExceptionInterface;
use RecursiveIteratorIterator;

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
     * @var \Mezzio\Navigation\ContainerInterface
     */
    private $container;

    /** @var string|null */
    private $navigation;

    /** @var \Laminas\Log\Logger */
    private $logger;

    /** @var HtmlifyInterface */
    private $htmlify;

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
     * @param \Interop\Container\ContainerInterface $serviceLocator
     * @param Logger                                $logger
     * @param HtmlifyInterface                      $htmlify
     */
    public function __construct(ContainerInterface $serviceLocator, Logger $logger, HtmlifyInterface $htmlify)
    {
        $this->serviceLocator = $serviceLocator;
        $this->logger         = $logger;
        $this->htmlify        = $htmlify;
    }

    /**
     * Helper entry point
     *
     * @param Navigation\ContainerInterface|string|null $container container to operate on
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return self
     */
    public function __invoke($container = null): self
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Sets navigation container the helper operates on by default
     *
     * Implements {@link HelperInterface::setContainer()}.
     *
     * @param Navigation\ContainerInterface|string|null $container default is null, meaning container will be reset
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return self
     */
    public function setContainer($container = null): self
    {
        $this->parseContainer($container);
        $this->container = $container;

        return $this;
    }

    /**
     * Returns the navigation container helper operates on by default
     *
     * Implements {@link HelperInterface::getContainer()}.
     *
     * If no container is set, a new container will be instantiated and
     * stored in the helper.
     *
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
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
     * Verifies container and eventually fetches it from service locator if it is a string
     *
     * @param Navigation\ContainerInterface|string|null $container
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return void
     */
    private function parseContainer(&$container = null): void
    {
        if (null === $container || $container instanceof Navigation\ContainerInterface) {
            return;
        }

        if (is_string($container)) {
            // Fallback
            if (in_array($container, ['default', 'navigation'], true)) {
                // Uses class name
                if ($this->serviceLocator->has(Navigation\Navigation::class)) {
                    try {
                        $container = $this->serviceLocator->get(Navigation\Navigation::class);
                    } catch (ContainerExceptionInterface $e) {
                        throw new Exception\InvalidArgumentException(
                            sprintf('Could not load Container "%s"', Navigation\Navigation::class),
                            0,
                            $e
                        );
                    }

                    return;
                }

                // Uses old service name
                if ($this->serviceLocator->has('navigation')) {
                    try {
                        $container = $this->serviceLocator->get('navigation');
                    } catch (ContainerExceptionInterface $e) {
                        throw new Exception\InvalidArgumentException(
                            'Could not load Container "navigation"',
                            0,
                            $e
                        );
                    }

                    return;
                }
            }

            /*
             * Load the navigation container from the root service locator
             */
            try {
                $container = $this->serviceLocator->get($container);
            } catch (ContainerExceptionInterface $e) {
                throw new Exception\InvalidArgumentException(
                    sprintf('Could not load Container "%s"', $container),
                    0,
                    $e
                );
            }

            return;
        }

        throw new Exception\InvalidArgumentException(
            'Container must be a string alias or an instance of Mezzio\Navigation\ContainerInterface'
        );
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
     * Implements {@link HelperInterface::__toString()}.
     *
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Validator\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function __toString(): string
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
     *
     * @return array an associative array with the values 'depth' and 'page',
     *               or an empty array if not found
     */
    final public function findActive($container, ?int $minDepth = null, ?int $maxDepth = -1): array
    {
        $this->setContainer($container);

        if (!$this->container instanceof Navigation\ContainerInterface) {
            return [];
        }

        if (!is_int($minDepth)) {
            $minDepth = $this->getMinDepth();
        }

        if ((!is_int($maxDepth) || 0 > $maxDepth) && null !== $maxDepth) {
            $maxDepth = $this->getMaxDepth();
        }

        $found      = null;
        $foundDepth = -1;
        $iterator   = new RecursiveIteratorIterator(
            $this->container,
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $page) {
            \assert(
                $page instanceof PageInterface,
                sprintf(
                    '$page should be an Instance of %s, but was %s',
                    PageInterface::class,
                    get_class($page)
                )
            );

            $currDepth = $iterator->getDepth();

            if ($currDepth < $minDepth || !$this->accept($page)) {
                // page is not accepted
                continue;
            }

            if (!$page->isActive(false) || $currDepth <= $foundDepth) {
                continue;
            }

            // found an active page at a deeper level than before
            $found      = $page;
            $foundDepth = $currDepth;
        }

        if (is_int($maxDepth) && $foundDepth > $maxDepth) {
            while ($foundDepth > $maxDepth) {
                if (--$foundDepth < $minDepth) {
                    $found = null;
                    break;
                }

                if (null === $found) {
                    break;
                }

                $found = $found->getParent();
                if (!$found instanceof PageInterface) {
                    $found = null;
                    break;
                }
            }
        }

        if ($found) {
            return ['page' => $found, 'depth' => $foundDepth];
        }

        return [];
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
        if (!$page->isVisible(false) && !$this->getRenderInvisible()) {
            return false;
        }

        $accept = true;

        if ($this->getUseAuthorization()) {
            $authorization = $this->getAuthorization();
            $role          = $this->getRole();
            $resource      = $page->getResource();

            if (null !== $authorization && null !== $role && null !== $resource) {
                $accept = $authorization->isGranted($role, $resource, $page->getPrivilege());
            }
        }

        if ($accept && $recursive) {
            $parent = $page->getParent();

            if ($parent instanceof PageInterface) {
                $accept = $this->accept($parent, true);
            }
        }

        return $accept;
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
     * Converts an associative array to a string of tag attributes.
     *
     * Overloads {@link \Laminas\View\Helper\AbstractHtmlElement::htmlAttribs()}.
     *
     * @param array $attribs an array where each key-value pair is converted
     *                       to an attribute name and value
     *
     * @return string
     */
    protected function htmlAttribs($attribs): string
    {
        // filter out null values and empty string values
        foreach ($attribs as $key => $value) {
            if (null !== $value && (!is_string($value) || mb_strlen($value))) {
                continue;
            }

            unset($attribs[$key]);
        }

        return parent::htmlAttribs($attribs);
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
     * Normalize an ID
     *
     * Overrides {@link \Laminas\View\Helper\AbstractHtmlElement::normalizeId()}.
     *
     * @param string $value
     *
     * @return string
     */
    protected function normalizeId($value): string
    {
        $prefix = static::class;
        $prefix = mb_strtolower(trim(mb_substr($prefix, (int) mb_strrpos($prefix, '\\')), '\\'));

        return $prefix . '-' . $value;
    }

    /**
     * Sets AuthorizationInterface to use when iterating pages
     * Implements {@link HelperInterface::setAuthorization()}.
     *
     * @param AuthorizationInterface|null $authorization AuthorizationInterface object
     *
     * @return void
     */
    final public function setAuthorization(?AuthorizationInterface $authorization = null): void
    {
        $this->authorization = $authorization;
    }

    /**
     * Returns AuthorizationInterface or null if it isn't set using {@link setAuthorization()} or
     * {@link setDefaultAuthorization()}
     *
     * Implements {@link HelperInterface::getAuthorization()}.
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
     * Implements {@link HelperInterface::hasAuthorization()}.
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
     * Implements {@link HelperInterface::hasContainer()}.
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
     * @return void
     */
    final public function setIndent($indent): void
    {
        $this->indent = $this->getWhitespace($indent);
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
     * @return void
     */
    final public function setMaxDepth(int $maxDepth): void
    {
        $this->maxDepth = $maxDepth;
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
     * @return void
     */
    final public function setMinDepth(int $minDepth): void
    {
        $this->minDepth = $minDepth;
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
     * @return void
     */
    final public function setRenderInvisible(bool $renderInvisible = true): void
    {
        $this->renderInvisible = $renderInvisible;
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
     * Implements {@link HelperInterface::setRole()}.
     *
     * @param string $role [optional] role to set. Expects a string or null. Default is null, which will set no role.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return void
     */
    final public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * Returns Authorization role to use when iterating pages, or null if it isn't set
     * using {@link setRole()} or {@link setDefaultRole()}
     *
     * Implements {@link HelperInterface::getRole()}.
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
     * Implements {@link HelperInterface::hasRole()}.
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
     * Implements {@link HelperInterface::setUseAuthorization()}.
     *
     * @param bool $useAuthorization
     *
     * @return void
     */
    final public function setUseAuthorization(bool $useAuthorization = true): void
    {
        $this->useAuthorization = $useAuthorization;
    }

    /**
     * Returns whether Authorization should be used
     * Implements {@link HelperInterface::getUseAuthorization()}.
     *
     * @return bool
     */
    final public function getUseAuthorization(): bool
    {
        return $this->useAuthorization;
    }

    // Static methods:

    /**
     * Sets default Authorization to use if another Authorization is not explicitly set
     *
     * @param \Mezzio\GenericAuthorization\AuthorizationInterface $authorization [optional] Authorization object. Default is null, which
     *                                                                           sets no Authorization object.
     *
     * @return void
     */
    final public static function setDefaultAuthorization(AuthorizationInterface $authorization): void
    {
        static::$defaultAuthorization = $authorization;
    }

    /**
     * Sets default Authorization role(s) to use when iterating pages if not explicitly
     * set later with {@link setRole()}
     *
     * @param string $role [optional] role to set. Expects null or string. Default is null, which
     *                     sets no default role.
     *
     * @return void
     */
    final public static function setDefaultRole(string $role): void
    {
        static::$defaultRole = $role;
    }
}
