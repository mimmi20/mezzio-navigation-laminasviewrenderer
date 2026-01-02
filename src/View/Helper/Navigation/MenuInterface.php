<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Laminas\View\Exception;
use Laminas\View\Model\ModelInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;

interface MenuInterface extends ViewHelperInterface
{
    /**
     * Renders helper.
     *
     * Renders a HTML 'ul' for the given $container. If $container is not given,
     * the container registered in the helper will be used.
     *
     * Available $options:
     *
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to create menu from. Default is to use the container retrieved from {@link getContainer()}.
     * @param array<string, bool|int|string|null>           $options   [optional] options for controlling rendering
     * @phpstan-param array{indent?: int|string|null, ulClass?: string|null, liClass?: string|null, minDepth?: int|null, maxDepth?: int|null, onlyActiveBranch?: bool, renderParents?: bool, escapeLabels?: bool, addClassToListItem?: bool, liActiveClass?: string|null} $options
     *
     * @throws Exception\InvalidArgumentException
     */
    public function renderMenu(ContainerInterface | string | null $container = null, array $options = []): string;

    /**
     * Renders the given $container by invoking the partial view helper.
     *
     * The container will simply be passed on as a model to the view script
     * as-is, and will be available in the partial script as 'container', e.g.
     * <code>echo 'Number of pages: ', count($this->container);</code>.
     *
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to pass to view script. Default is to use the container registered in the helper.
     * @param array<int, string>|string|null                $partial   [optional] partial view script to use. Default is to use the partial registered in the helper. If an array is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     */
    public function renderPartial(
        ContainerInterface | string | null $container = null,
        array | string | null $partial = null,
    ): string;

    /**
     * Renders the given $container by invoking the partial view helper with the given parameters as the model.
     *
     * The container will simply be passed on as a model to the view script
     * as-is, and will be available in the partial script as 'container', e.g.
     * <code>echo 'Number of pages: ', count($this->container);</code>.
     *
     * Any parameters provided will be passed to the partial via the view model.
     *
     * @param array<int|string, mixed>                      $params
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to pass to view script. Default is to use the container registered in the helper.
     * @param array<int, string>|string|null                $partial   [optional] partial view script to use. Default is to use the partial registered in the helper. If an array is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     */
    public function renderPartialWithParams(
        array $params = [],
        ContainerInterface | string | null $container = null,
        array | string | null $partial = null,
    ): string;

    /**
     * Renders the inner-most sub menu for the active page in the $container.
     *
     * This is a convenience method which is equivalent to the following call:
     * <code>
     * renderMenu($container, array(
     *     'indent'           => $indent,
     *     'ulClass'          => $ulClass,
     *     'minDepth'         => null,
     *     'maxDepth'         => null,
     *     'onlyActiveBranch' => true,
     *     'renderParents'    => false,
     *     'liActiveClass'    => $liActiveClass
     * ));
     * </code>
     *
     * @param ContainerInterface<PageInterface>|null $container     [optional] container to render. Default is to render the container registered in the helper.
     * @param string|null                            $ulClass       [optional] CSS class to use for UL element. Default is to use the value from {@link getUlClass()}.
     * @param string|null                            $liClass       [optional] CSS class to use for LI elements. Default is to use the value from {@link getLiClass()}.
     * @param int|string|null                        $indent        [optional] indentation as a string or number of spaces. Default is to use the value retrieved from {@link getIndent()}.
     * @param string|null                            $liActiveClass [optional] CSS class to use for UL element. Default is to use the value from {@link getUlClass()}.
     *
     * @throws Exception\InvalidArgumentException
     */
    public function renderSubMenu(
        ContainerInterface | null $container = null,
        string | null $ulClass = null,
        string | null $liClass = null,
        int | string | null $indent = null,
        string | null $liActiveClass = null,
    ): string;

    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty.
     *
     * Overrides {@link AbstractHelper::htmlify()}.
     *
     * @param PageInterface $page               page to generate HTML for
     * @param bool          $escapeLabel        Whether to escape the label
     * @param bool          $addClassToListItem Whether to add the page class to the list item
     *
     * @throws void
     */
    public function htmlify(PageInterface $page, bool $escapeLabel = true, bool $addClassToListItem = false): string;

    /**
     * Sets a flag indicating whether labels should be escaped.
     *
     * @param bool $flag [optional] escape labels
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function escapeLabels(bool $flag = true);

    /**
     * Enables/disables page class applied to <li> element.
     *
     * @param bool $flag [optional] page class applied to <li> element Default
     *                   is true
     *
     * @return self fluent interface, returns self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setAddClassToListItem(bool $flag = true);

    /**
     * Returns flag indicating whether page class should be applied to <li> element.
     *
     * By default, this value is false.
     *
     * @return bool whether parents should be rendered
     *
     * @throws void
     */
    public function getAddClassToListItem(): bool;

    /**
     * Sets a flag indicating whether only active branch should be rendered.
     *
     * @param bool $flag [optional] render only active branch
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setOnlyActiveBranch(bool $flag = true);

    /**
     * Returns a flag indicating whether only active branch should be rendered.
     *
     * By default, this value is false, meaning the entire menu will be rendered.
     *
     * @throws void
     */
    public function getOnlyActiveBranch(): bool;

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array<int, string>|ModelInterface|string|null $partial partial view script or null. If an array is given, the first value is used for the partial view script.
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function setPartial($partial);

    /**
     * Returns partial view script to use for rendering menu.
     *
     * @return array<int, string>|ModelInterface|string|null
     *
     * @throws void
     */
    public function getPartial(): array | ModelInterface | string | null;

    /**
     * Enables/disables rendering of parents when only rendering active branch.
     *
     * See {@link setOnlyActiveBranch()} for more information.
     *
     * @param bool $flag [optional] render parents when rendering active branch
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setRenderParents(bool $flag = true);

    /**
     * Returns flag indicating whether parents should be rendered when rendering only the active branch.
     *
     * By default, this value is true.
     *
     * @throws void
     */
    public function getRenderParents(): bool;

    /**
     * Sets CSS class to use for the first 'ul' element when rendering.
     *
     * @param string $ulClass CSS class to set
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setUlClass(string $ulClass);

    /**
     * Returns CSS class to use for the first 'ul' element when rendering.
     *
     * @throws void
     */
    public function getUlClass(): string;

    /**
     * Sets CSS class to use for the 'li' elements when rendering.
     *
     * @param string $liClass CSS class to set
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setLiClass(string $liClass);

    /**
     * Returns CSS class to use for the 'li' elements when rendering.
     *
     * @throws void
     */
    public function getLiClass(): string;

    /**
     * Sets CSS class to use for the active 'li' element when rendering.
     *
     * @param string $liActiveClass CSS class to set
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setLiActiveClass(string $liActiveClass);

    /**
     * Returns CSS class to use for the active 'li' element when rendering.
     *
     * @throws void
     */
    public function getLiActiveClass(): string;
}
