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

use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\Partial;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Page\PageInterface;

/**
 * Helper for printing breadcrumbs.
 */
final class Breadcrumbs extends AbstractHtmlElement implements BreadcrumbsInterface
{
    use HelperTrait;

    /**
     * Whether last page in breadcrumb should be hyperlinked.
     *
     * @var bool
     */
    private $linkLast = false;

    /**
     * Partial view script to use for rendering menu.
     *
     * @var array|string|null
     */
    private $partial;

    /**
     * Breadcrumbs separator string.
     *
     * @var string
     */
    private $separator = ' &gt; ';

    /**
     * Renders helper.
     *
     * Implements {@link HelperInterface::render()}.
     *
     * @param ContainerInterface|null $container [optional] container to render.
     *                                           Default is null, which indicates
     *                                           that the helper should render
     *                                           the container returned by {@link getContainer()}.
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function render(?ContainerInterface $container = null): string
    {
        $partial = $this->getPartial();

        if ($partial) {
            return $this->renderPartial($container, $partial);
        }

        return $this->renderStraight($container);
    }

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper.
     *
     * @param ContainerInterface|null $container [optional] container to render. Default is
     *                                           to render the container registered in the helper.
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderStraight(?ContainerInterface $container = null): string
    {
        $this->parseContainer($container);
        if (null === $container) {
            $container = $this->getContainer();
        }

        // find deepest active
        if (!$active = $this->findActive($container)) {
            return '';
        }

        $active = $active['page'];

        // put the deepest active page last in breadcrumbs
        if ($this->getLinkLast()) {
            $html = $this->htmlify($active);
        } else {
            $escaper = $this->getView()->plugin('escapeHtml');
            \assert($escaper instanceof EscapeHtml);

            $html = $escaper(
                $this->translate($active->getLabel(), $active->getTextDomain())
            );
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof PageInterface) {
                // prepend crumb to html
                $html = $this->htmlify($parent)
                    . $this->getSeparator()
                    . $html;
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
     * Renders the given $container by invoking the partial view helper.
     *
     * The container will simply be passed on as a model to the view script
     * as-is, and will be available in the partial script as 'container', e.g.
     * <code>echo 'Number of pages: ', count($this->container);</code>.
     *
     * @param ContainerInterface|null $container [optional] container to pass to view
     *                                           script. Default is to use the container registered in the helper.
     * @param array|string|null       $partial   [optional] partial view script to use.
     *                                           Default is to use the partial registered in the helper. If an array
     *                                           is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException                            if no partial provided
     * @throws Exception\InvalidArgumentException                    if partial is invalid array
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderPartial(?ContainerInterface $container = null, $partial = null): string
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
     * @param ContainerInterface|null $container [optional] container to pass to view
     *                                           script. Default is to use the container registered in the helper.
     * @param array|string|null       $partial   [optional] partial view script to use.
     *                                           Default is to use the partial registered in the helper. If an array
     *                                           is given, the first value is used for the partial view script.
     * @param array                   $params
     *
     * @throws Exception\RuntimeException                            if no partial provided
     * @throws Exception\InvalidArgumentException                    if partial is invalid array
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderPartialWithParams(array $params = [], ?ContainerInterface $container = null, $partial = null): string
    {
        return $this->renderPartialModel($params, $container, $partial);
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
        $this->linkLast = (bool) $linkLast;

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
     * @param array|string|null $partial partial view script or null. If an array is
     *                                   given, the first value is used for the partial view script.
     *
     * @return self
     */
    public function setPartial($partial): self
    {
        if (null === $partial || is_string($partial) || is_array($partial)) {
            $this->partial = $partial;
        }

        return $this;
    }

    /**
     * Returns partial view script to use for rendering menu.
     *
     * @return array|string|null
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
        if (is_string($separator)) {
            $this->separator = $separator;
        }

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
     * @param array                   $params
     * @param ContainerInterface|null $container
     * @param array|string|null       $partial
     *
     * @throws Exception\RuntimeException                            if no partial provided
     * @throws Exception\InvalidArgumentException                    if partial is invalid array
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    private function renderPartialModel(array $params, ?ContainerInterface $container, $partial): string
    {
        $this->parseContainer($container);
        if (null === $container) {
            $container = $this->getContainer();
        }

        if (null === $partial) {
            $partial = $this->getPartial();
        }

        if (empty($partial)) {
            throw new Exception\RuntimeException(
                'Unable to render breadcrumbs: No partial view script provided'
            );
        }

        $model  = array_merge($params, ['pages' => []], ['separator' => $this->getSeparator()]);
        $active = $this->findActive($container);
        if ($active) {
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

        $partialHelper = $this->getView()->plugin('partial');
        \assert($partialHelper instanceof Partial);
        if (is_array($partial)) {
            if (2 !== count($partial)) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render breadcrumbs: A view partial supplied as '
                    . 'an array must contain one value: the partial view script'
                );
            }

            $partial = $partial[0];
        }

        $rendered = $partialHelper($partial, $model);

        if ($rendered instanceof Partial) {
            throw new Exception\InvalidArgumentException(
                'Unable to render menu: A view partial was not rendered correctly'
            );
        }

        return $rendered;
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @return int|null
     */
    public function getMinDepth(): ?int
    {
        if (!is_int($this->minDepth) || 1 > $this->minDepth) {
            return 1;
        }

        return $this->minDepth;
    }
}
