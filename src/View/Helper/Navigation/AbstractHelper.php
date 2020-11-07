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
use Laminas\View;
use Laminas\View\Exception;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Helper\TranslatorAwareTrait;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation;
use Mezzio\Navigation\Page\PageInterface;
use Psr\Container\ContainerExceptionInterface;
use RecursiveIteratorIterator;

/**
 * Base class for navigational helpers.
 *
 * Duck-types against Laminas\I18n\Translator\TranslatorAwareInterface.
 */
abstract class AbstractHelper extends View\Helper\AbstractHtmlElement implements HelperInterface
{
    use TranslatorAwareTrait;

    /**
     * ContainerInterface to operate on by default
     *
     * @var \Mezzio\Navigation\ContainerInterface
     */
    protected $container;

    /** @var string|null */
    protected $navigation;

    /** @var \Laminas\Log\Logger */
    protected $logger;

    /**
     * The minimum depth a page must have to be included when rendering
     *
     * @var int|null
     */
    protected $minDepth;

    /**
     * The maximum depth a page can have to be included when rendering
     *
     * @var int|null
     */
    protected $maxDepth;

    /**
     * Indentation string
     *
     * @var string
     */
    protected $indent = '';

    /**
     * Authorization to use when iterating pages
     *
     * @var \Mezzio\GenericAuthorization\AuthorizationInterface|null
     */
    protected $authorization;

    /**
     * Whether invisible items should be rendered by this helper
     *
     * @var bool
     */
    protected $renderInvisible = false;

    /**
     * Authorization role to use when iterating pages
     *
     * @var string|null
     */
    protected $role;

    /** @var ContainerInterface */
    protected $serviceLocator;

    /**
     * Whether Authorization should be used for filtering out pages
     *
     * @var bool
     */
    protected $useAuthorization = true;

    /**
     * Default Authorization role to use when iterating pages if not explicitly set in the
     * instance by calling {@link setRole()}
     *
     * @var string|null
     */
    protected static $defaultRole;

    /** @var AuthorizationInterface|null */
    protected static $defaultAuthorization;

    /**
     * @param \Interop\Container\ContainerInterface $serviceLocator
     * @param Logger                                $logger
     */
    public function __construct(
        ContainerInterface $serviceLocator,
        Logger $logger
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->logger         = $logger;
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
     * @return AbstractHelper
     */
    public function setContainer($container = null)
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
    protected function parseContainer(&$container = null): void
    {
        if (null === $container) {
            return;
        }

        if (is_string($container)) {
            $services = $this->getServiceLocator();

            if (!$services) {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        'Attempted to set container with alias "%s" but no ServiceLocator was set',
                        $container
                    )
                );
            }

            // Fallback
            if (in_array($container, ['default', 'navigation'], true)) {
                // Uses class name
                if ($services->has(Navigation\Navigation::class)) {
                    try {
                        $container = $services->get(Navigation\Navigation::class);
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
                if ($services->has('navigation')) {
                    try {
                        $container = $services->get('navigation');
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
                $container = $services->get($container);
            } catch (ContainerExceptionInterface $e) {
                throw new Exception\InvalidArgumentException(
                    sprintf('Could not load Container "%s"', $container),
                    0,
                    $e
                );
            }

            return;
        }

        if (!$container instanceof Navigation\ContainerInterface) {
            throw new Exception\InvalidArgumentException(
                'Container must be a string alias or an instance of Mezzio\Navigation\ContainerInterface'
            );
        }
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
     * @return array an associative array with
     *               the values 'depth' and
     *               'page', or an empty array
     *               if not found
     */
    final public function findActive($container, ?int $minDepth = null, ?int $maxDepth = -1): array
    {
        $this->parseContainer($container);
        if (!is_int($minDepth)) {
            $minDepth = $this->getMinDepth();
        }

        if ((!is_int($maxDepth) || 0 > $maxDepth) && null !== $maxDepth) {
            $maxDepth = $this->getMaxDepth();
        }

        $found      = null;
        $foundDepth = -1;
        $iterator   = new RecursiveIteratorIterator(
            $container,
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $page) {
            \assert($page instanceof \Mezzio\Navigation\Page\PageInterface);
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
    protected function getWhitespace($indent): string
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }

        return (string) $indent;
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * Overloads {@link View\Helper\AbstractHtmlElement::htmlAttribs()}.
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
        $label = $this->translate((string) $page->getLabel(), $page->getTextDomain());
        $title = $this->translate((string) $page->getTitle(), $page->getTextDomain());

        // get attribs for anchor element
        $attribs = [
            'id' => $page->getId(),
            'title' => $title,
            'class' => $page->getClass(),
            'href' => $page->getHref(),
            'target' => $page->getTarget(),
        ];

        $escaper = $this->getView()->plugin('escapeHtml');
        \assert($escaper instanceof \Laminas\View\Helper\EscapeHtml);
        $label = $escaper($label);

        return '<a' . $this->htmlAttribs($attribs) . '>' . $label . '</a>';
    }

    /**
     * Translate a message (for label, title, …)
     *
     * @param string      $message    ID of the message to translate
     * @param string|null $textDomain Text domain (category name for the translations)
     *
     * @return string Translated message
     */
    protected function translate(string $message, ?string $textDomain = null): string
    {
        if (!is_string($message) || empty($message)) {
            return $message;
        }

        if (!$this->isTranslatorEnabled() || !$this->hasTranslator()) {
            return $message;
        }

        $translator = $this->getTranslator();

        if (null === $translator) {
            return $message;
        }

        $textDomain = $textDomain ?: $this->getTranslatorTextDomain();

        return $translator->translate($message, $textDomain);
    }

    /**
     * Normalize an ID
     *
     * Overrides {@link View\Helper\AbstractHtmlElement::normalizeId()}.
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
     * Set the service locator.
     * Used internally to pull named navigation containers to render.
     *
     * @param ContainerInterface $container
     *
     * @return AbstractHelper
     */
    final public function setServiceLocator(ContainerInterface $container)
    {
        $this->serviceLocator = $container;

        return $this;
    }

    /**
     * Get the service locator.
     *
     * Used internally to pull named navigation containers to render.
     *
     * @return ContainerInterface|null
     */
    final public function getServiceLocator(): ?ContainerInterface
    {
        return $this->serviceLocator;
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
     * @throws Exception\InvalidArgumentException if role is invalid
     *
     * @return void
     */
    final public static function setDefaultRole(string $role): void
    {
        static::$defaultRole = $role;
    }
}