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
use Laminas\View\Model\ModelInterface;
use Mezzio\Navigation\ContainerInterface;

interface BreadcrumbsInterface extends ViewHelperInterface
{
    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper.
     *
     * @param ContainerInterface|string|null $container [optional] container to render. Default is
     *                                                  to render the container registered in the helper.
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderStraight($container = null): string;

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
     * @throws Exception\RuntimeException                            if no partial provided
     * @throws Exception\InvalidArgumentException                    if partial is invalid array
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderPartial($container = null, $partial = null): string;

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
     * @throws Exception\RuntimeException                            if no partial provided
     * @throws Exception\InvalidArgumentException                    if partial is invalid array
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderPartialWithParams(array $params = [], $container = null, $partial = null): string;

    /**
     * Sets whether last page in breadcrumbs should be hyperlinked.
     *
     * @param bool $linkLast whether last page should be hyperlinked
     *
     * @return self
     */
    public function setLinkLast(bool $linkLast);

    /**
     * Returns whether last page in breadcrumbs should be hyperlinked.
     *
     * @return bool
     */
    public function getLinkLast(): bool;

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array|ModelInterface|string|null $partial partial view script or null. If an array is
     *                                                  given, the first value is used for the partial view script.
     *
     * @return self
     */
    public function setPartial($partial);

    /**
     * Returns partial view script to use for rendering menu.
     *
     * @return array|ModelInterface|string|null
     */
    public function getPartial();

    /**
     * Sets breadcrumb separator.
     *
     * @param string $separator separator string
     *
     * @return self
     */
    public function setSeparator(string $separator);

    /**
     * Returns breadcrumb separator.
     *
     * @return string breadcrumb separator
     */
    public function getSeparator(): string;
}
