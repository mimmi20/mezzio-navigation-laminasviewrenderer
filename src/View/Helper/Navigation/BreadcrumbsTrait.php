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

use Laminas\I18n\View\Helper\Translate;
use Laminas\Log\Logger;
use Laminas\View\Exception;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Model\ModelInterface;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Page\PageInterface;

/**
 * Helper for printing breadcrumbs.
 */
trait BreadcrumbsTrait
{
    /**
     * Whether last page in breadcrumb should be hyperlinked.
     *
     * @var bool
     */
    private $linkLast = false;

    /**
     * Partial view script to use for rendering menu.
     *
     * @var array|ModelInterface|string|null
     */
    private $partial;

    /**
     * Breadcrumbs separator string.
     *
     * @var string
     */
    private $separator = ' &gt; ';

    /** @var Translate|null */
    private $translator;

    /** @var EscapeHtml */
    private $escaper;

    /** @var LaminasViewRenderer */
    private $renderer;

    /**
     * @param \Interop\Container\ContainerInterface    $serviceLocator
     * @param Logger                                   $logger
     * @param HtmlifyInterface                         $htmlify
     * @param ContainerParserInterface                 $containerParser
     * @param EscapeHtml                               $escaper
     * @param LaminasViewRenderer                      $renderer
     * @param \Laminas\I18n\View\Helper\Translate|null $translator
     */
    public function __construct(
        \Interop\Container\ContainerInterface $serviceLocator,
        Logger $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        EscapeHtml $escaper,
        LaminasViewRenderer $renderer,
        ?Translate $translator = null
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;
        $this->translator      = $translator;
        $this->escaper         = $escaper;
        $this->renderer        = $renderer;
    }

    /**
     * Renders helper.
     *
     * Implements {@link ViewHelperInterface::render()}.
     *
     * @param ContainerInterface|string|null $container [optional] container to render.
     *                                                  Default is null, which indicates
     *                                                  that the helper should render
     *                                                  the container returned by {@link getContainer()}.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     *
     * @return string
     */
    public function render($container = null): string
    {
        $partial = $this->getPartial();

        if ($partial) {
            return $this->renderPartial($container, $partial);
        }

        return $this->renderStraight($container);
    }

    /**
     * Renders the given $container by invoking the partial view helper.
     *
     * The container will simply be passed on as a model to the view script
     * as-is, and will be available in the partial script as 'container', e.g.
     * <code>echo 'Number of pages: ', count($this->container);</code>.
     *
     * @param ContainerInterface|string|null   $container [optional] container to pass to view
     *                                                    script. Default is to use the container registered in the helper.
     * @param array|ModelInterface|string|null $partial   [optional] partial view script to use.
     *                                                    Default is to use the partial registered in the helper. If an array
     *                                                    is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     *
     * @return string
     */
    public function renderPartial($container = null, $partial = null): string
    {
        return $this->renderPartialModel([], $container, $partial);
    }

    /**
     * Renders the given $container by invoking the partial view helper with the given parameters as the model.
     *
     * The container will simply be passed on as a model to the view script
     * as-is, and will be available in the partial script as 'container', e.g.
     * <code>echo 'Number of pages: ', count($this->container);</code>.
     *
     * Any parameters provided will be passed to the partial via the view model.
     *
     * @param ContainerInterface|string|null   $container [optional] container to pass to view
     *                                                    script. Default is to use the container registered in the helper.
     * @param array|ModelInterface|string|null $partial   [optional] partial view script to use.
     *                                                    Default is to use the partial registered in the helper. If an array
     *                                                    is given, the first value is used for the partial view script.
     * @param array                            $params
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     *
     * @return string
     */
    public function renderPartialWithParams(array $params = [], $container = null, $partial = null): string
    {
        return $this->renderPartialModel($params, $container, $partial);
    }

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper.
     *
     * @param ContainerInterface|string|null $container [optional] container to render. Default is
     *                                                  to render the container registered in the helper.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderStraight($container = null): string
    {
        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        // find deepest active
        $active = $this->findActive($container);

        if (!$active) {
            return '';
        }

        $active = $active['page'];

        // put the deepest active page last in breadcrumbs
        if ($this->getLinkLast()) {
            $html = $this->renderBreadcrumbItem(
                $this->htmlify->toHtml(self::class, $active),
                $active->getLiClass() ?? '',
                $active->isActive()
            );
        } else {
            $label = (string) $active->getLabel();

            if (null !== $this->translator) {
                $label = ($this->translator)($label, $active->getTextDomain());
            }

            $html = $this->renderBreadcrumbItem(
                ($this->escaper)($label)
            );
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof PageInterface) {
                // prepend crumb to html
                $entry = $this->renderBreadcrumbItem(
                    $this->htmlify->toHtml(self::class, $parent),
                    $parent->getLiClass() ?? '',
                    $parent->isActive()
                );
                $html = $entry . $this->getSeparator() . $html;
            }

            if ($parent === $container) {
                // at the root of the given container
                break;
            }

            $active = $parent;
        }

        return mb_strlen($html) ? $this->getIndent() . $html : '';
    }

    /**
     * Sets whether last page in breadcrumbs should be hyperlinked.
     *
     * @param bool $linkLast whether last page should be hyperlinked
     *
     * @return self
     */
    public function setLinkLast(bool $linkLast): self
    {
        $this->linkLast = $linkLast;

        return $this;
    }

    /**
     * Returns whether last page in breadcrumbs should be hyperlinked.
     *
     * @return bool
     */
    public function getLinkLast(): bool
    {
        return $this->linkLast;
    }

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array|ModelInterface|string|null $partial partial view script or null. If an array is
     *                                                  given, the first value is used for the partial view script.
     *
     * @return self
     */
    public function setPartial($partial): self
    {
        if (null === $partial || is_string($partial) || is_array($partial) || $partial instanceof ModelInterface) {
            $this->partial = $partial;
        }

        return $this;
    }

    /**
     * Returns partial view script to use for rendering menu.
     *
     * @return array|ModelInterface|string|null
     */
    public function getPartial()
    {
        return $this->partial;
    }

    /**
     * Sets breadcrumb separator.
     *
     * @param string $separator separator string
     *
     * @return self
     */
    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Returns breadcrumb separator.
     *
     * @return string breadcrumb separator
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * Render a partial with the given "model".
     *
     * @param array                            $params
     * @param ContainerInterface|string|null   $container
     * @param array|ModelInterface|string|null $partial
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     *
     * @return string
     */
    private function renderPartialModel(array $params, $container, $partial): string
    {
        if (null === $partial) {
            $partial = $this->getPartial();
        }

        if (null === $partial || '' === $partial || [] === $partial) {
            throw new Exception\RuntimeException(
                'Unable to render breadcrumbs: No partial view script provided'
            );
        }

        if (is_array($partial)) {
            if (2 !== count($partial)) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render breadcrumbs: A view partial supplied as '
                    . 'an array must contain one value: the partial view script'
                );
            }

            $partial = $partial[0];
        }

        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        $model  = array_merge($params, ['pages' => []], ['separator' => $this->getSeparator()]);
        $active = $this->findActive($container);

        if ([] !== $active) {
            $active           = $active['page'];
            $model['pages'][] = $active;

            while ($parent = $active->getParent()) {
                if (!$parent instanceof PageInterface) {
                    break;
                }

                $model['pages'][] = $parent;

                if ($parent === $container) {
                    // break if at the root of the given container
                    break;
                }

                $active = $parent;
            }

            $model['pages'] = array_reverse($model['pages']);
        }

        return $this->renderer->render($partial, $model);
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @return int|null
     */
    public function getMinDepth(): ?int
    {
        if (!is_int($this->minDepth) || 0 > $this->minDepth) {
            return 1;
        }

        return $this->minDepth;
    }
}
