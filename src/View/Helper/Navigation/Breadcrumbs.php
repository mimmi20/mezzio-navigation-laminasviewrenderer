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
use Mezzio\Navigation\AbstractContainer;
use Mezzio\Navigation\Page\AbstractPage;

/**
 * Helper for printing breadcrumbs.
 */
final class Breadcrumbs extends AbstractHelper
{
    /**
     * Whether last page in breadcrumb should be hyperlinked.
     *
     * @var bool
     */
    protected $linkLast = false;

    /**
     * The minimum depth a page must have to be included when rendering.
     *
     * @var int
     */
    protected $minDepth = 1;

    /**
     * Partial view script to use for rendering menu.
     *
     * @var array|string
     */
    protected $partial;

    /**
     * Breadcrumbs separator string.
     *
     * @var string
     */
    protected $separator = ' &gt; ';

    /**
     * Helper entry point.
     *
     * @param AbstractContainer|string|null $container container to operate on
     *
     * @return Breadcrumbs
     */
    public function __invoke($container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Renders helper.
     *
     * Implements {@link HelperInterface::render()}.
     *
     * @param AbstractContainer|string|null $container [optional] container to render.
     *                                                 Default is null, which indicates
     *                                                 that the helper should render
     *                                                 the container returned by {@link *                                         getContainer()}.
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
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper.
     *
     * @param AbstractContainer $container [optional] container to render. Default is
     *                                     to render the container registered in the helper.
     *
     * @return string
     */
    public function renderStraight($container = null): string
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
            $escaper = $this->view->plugin('escapeHtml');
            \assert($escaper instanceof \Laminas\View\Helper\EscapeHtml);
            $html = $escaper(
                $this->translate($active->getLabel(), $active->getTextDomain())
            );
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof AbstractPage) {
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
     * @param AbstractContainer|null $container [optional] container to pass to view
     *                                          script. Default is to use the container registered in the helper.
     * @param array|string|null      $partial   [optional] partial view script to use.
     *                                          Default is to use the partial registered in the helper. If an array
     *                                          is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     *
     * @return string
     */
    public function renderPartial($container = null, $partial = null)
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
     * @param AbstractContainer|null $container [optional] container to pass to view
     *                                          script. Default is to use the container registered in the helper.
     * @param array|string|null      $partial   [optional] partial view script to use.
     *                                          Default is to use the partial registered in the helper. If an array
     *                                          is given, the first value is used for the partial view script.
     * @param array                  $params
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     *
     * @return string
     */
    public function renderPartialWithParams(array $params = [], $container = null, $partial = null)
    {
        return $this->renderPartialModel($params, $container, $partial);
    }

    /**
     * Sets whether last page in breadcrumbs should be hyperlinked.
     *
     * @param bool $linkLast whether last page should be hyperlinked
     *
     * @return Breadcrumbs
     */
    public function setLinkLast($linkLast)
    {
        $this->linkLast = (bool) $linkLast;

        return $this;
    }

    /**
     * Returns whether last page in breadcrumbs should be hyperlinked.
     *
     * @return bool
     */
    public function getLinkLast()
    {
        return $this->linkLast;
    }

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array|string $partial partial view script or null. If an array is
     *                              given, the first value is used for the partial view script.
     *
     * @return Breadcrumbs
     */
    public function setPartial($partial)
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
     * @return Breadcrumbs
     */
    public function setSeparator($separator)
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
     * @param array                  $params
     * @param AbstractContainer|null $container
     * @param array|string|null      $partial
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     *
     * @return string
     */
    protected function renderPartialModel(array $params, $container, $partial): string
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
                if (!$parent instanceof AbstractPage) {
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

        $partialHelper = $this->view->plugin('partial');
        \assert($partialHelper instanceof \Laminas\View\Helper\Partial);
        if (is_array($partial)) {
            if (2 !== count($partial)) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render breadcrumbs: A view partial supplied as '
                    . 'an array must contain one value: the partial view script'
                );
            }

            return $partialHelper($partial[0], $model);
        }

        return $partialHelper($partial, $model);
    }
}
