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
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Laminas\View\Exception;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Model\ModelInterface;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function array_merge;
use function assert;
use function count;
use function is_array;
use function is_int;
use function is_string;

trait MenuTrait
{
    /**
     * Whether page class should be applied to <li> element.
     */
    private bool $addClassToListItem = false;

    /**
     * Whether labels should be escaped.
     */
    private bool $escapeLabels = true;

    /**
     * Whether only active branch should be rendered.
     */
    private bool $onlyActiveBranch = false;

    /**
     * Partial view script to use for rendering menu.
     *
     * @var array<int, string>|ModelInterface|string|null
     */
    private array | ModelInterface | string | null $partial = null;

    /**
     * Whether parents should be rendered when only rendering active branch.
     */
    private bool $renderParents = true;

    /**
     * CSS class to use for the ul element.
     */
    private string $ulClass = 'navigation';

    /**
     * CSS class to use for the li elements.
     */
    private string $liClass = '';

    /**
     * CSS class to use for the active li element.
     */
    private string $liActiveClass = 'active';

    /** @throws void */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        LoggerInterface $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        private EscapeHtmlAttr $escaper,
        private PartialRendererInterface $renderer,
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;
    }

    /**
     * Renders menu.
     *
     * Implements {@link ViewHelperInterface::render()}.
     *
     * If a partial view is registered in the helper, the menu will be rendered
     * using the given partial script. If no partial is registered, the menu
     * will be rendered as an 'ul' element by the helper's internal method.
     *
     * @see renderPartial()
     * @see renderMenu()
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
    public function render(ContainerInterface | string | null $container = null): string
    {
        $partial = $this->getPartial();

        if ($partial) {
            return $this->renderPartial($container, $partial);
        }

        return $this->renderMenu($container);
    }

    /**
     * Renders the given $container by invoking the partial view helper.
     *
     * The container will simply be passed on as a model to the view script
     * as-is, and will be available in the partial script as 'container', e.g.
     * <code>echo 'Number of pages: ', count($this->container);</code>.
     *
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to pass to view
     *                                                  script. Default is to use the container registered in the helper.
     * @param array<int, string>|ModelInterface|string|null $partial   [optional] partial view script to use.
     *                                                                 Default is to use the partial registered in the helper. If an array
     *                                                                 is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     * @throws InvalidArgumentException
     */
    public function renderPartial(
        ContainerInterface | string | null $container = null,
        array | ModelInterface | string | null $partial = null,
    ): string {
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
     * @param array<mixed>                                  $params
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to pass to view
     *                                                  script. Default is to use the container registered in the helper.
     * @param array<int, string>|string|null                $partial   [optional] partial view script to use.
     *                                                                 Default is to use the partial registered in the helper. If an array
     *                                                                 is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     * @throws InvalidArgumentException
     */
    public function renderPartialWithParams(
        array $params = [],
        ContainerInterface | string | null $container = null,
        array | string | null $partial = null,
    ): string {
        return $this->renderPartialModel($params, $container, $partial);
    }

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
     * @throws RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function htmlify(PageInterface $page, bool $escapeLabel = true, bool $addClassToListItem = false): string
    {
        return $this->htmlify->toHtml(self::class, $page, $escapeLabel, $addClassToListItem);
    }

    /**
     * Sets a flag indicating whether labels should be escaped.
     *
     * @param bool $flag [optional] escape labels
     *
     * @throws void
     */
    public function escapeLabels(bool $flag = true): self
    {
        $this->escapeLabels = $flag;

        return $this;
    }

    /** @throws void */
    public function getEscapeLabels(): bool
    {
        return $this->escapeLabels;
    }

    /**
     * Enables/disables page class applied to <li> element.
     *
     * @param bool $flag [optional] page class applied to <li> element Default
     *                   is true
     *
     * @return self fluent interface
     *
     * @throws void
     */
    public function setAddClassToListItem(bool $flag = true): self
    {
        $this->addClassToListItem = $flag;

        return $this;
    }

    /**
     * Returns flag indicating whether page class should be applied to <li> element.
     *
     * By default, this value is false.
     *
     * @return bool whether parents should be rendered
     *
     * @throws void
     */
    public function getAddClassToListItem(): bool
    {
        return $this->addClassToListItem;
    }

    /**
     * Sets a flag indicating whether only active branch should be rendered.
     *
     * @param bool $flag [optional] render only active branch
     *
     * @throws void
     */
    public function setOnlyActiveBranch(bool $flag = true): self
    {
        $this->onlyActiveBranch = $flag;

        return $this;
    }

    /**
     * Returns a flag indicating whether only active branch should be rendered.
     *
     * By default, this value is false, meaning the entire menu will be rendered.
     *
     * @throws void
     */
    public function getOnlyActiveBranch(): bool
    {
        return $this->onlyActiveBranch;
    }

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array<int, string>|ModelInterface|string|null $partial partial view script or null. If an array
     *                                                               is given, the first value is used for the partial view script.
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function setPartial($partial): self
    {
        if (
            $partial === null
            || is_string($partial)
            || is_array($partial)
            || $partial instanceof ModelInterface
        ) {
            $this->partial = $partial;
        }

        return $this;
    }

    /**
     * Returns partial view script to use for rendering menu.
     *
     * @return array<int, string>|ModelInterface|string|null
     *
     * @throws void
     */
    public function getPartial(): array | ModelInterface | string | null
    {
        return $this->partial;
    }

    /**
     * Enables/disables rendering of parents when only rendering active branch.
     *
     * See {@link setOnlyActiveBranch()} for more information.
     *
     * @param bool $flag [optional] render parents when rendering active branch
     *
     * @throws void
     */
    public function setRenderParents(bool $flag = true): self
    {
        $this->renderParents = $flag;

        return $this;
    }

    /**
     * Returns flag indicating whether parents should be rendered when rendering only the active branch.
     *
     * By default, this value is true.
     *
     * @throws void
     */
    public function getRenderParents(): bool
    {
        return $this->renderParents;
    }

    /**
     * Sets CSS class to use for the first 'ul' element when rendering.
     *
     * @param string $ulClass CSS class to set
     *
     * @throws void
     */
    public function setUlClass(string $ulClass): self
    {
        $this->ulClass = $ulClass;

        return $this;
    }

    /**
     * Returns CSS class to use for the first 'ul' element when rendering.
     *
     * @throws void
     */
    public function getUlClass(): string
    {
        return $this->ulClass;
    }

    /**
     * Sets CSS class to use for the 'li' elements when rendering.
     *
     * @param string $liClass CSS class to set
     *
     * @throws void
     */
    public function setLiClass(string $liClass): self
    {
        $this->liClass = $liClass;

        return $this;
    }

    /**
     * Returns CSS class to use for the 'li' elements when rendering.
     *
     * @throws void
     */
    public function getLiClass(): string
    {
        return $this->liClass;
    }

    /**
     * Sets CSS class to use for the active 'li' element when rendering.
     *
     * @param string $liActiveClass CSS class to set
     *
     * @throws void
     */
    public function setLiActiveClass(string $liActiveClass): self
    {
        $this->liActiveClass = $liActiveClass;

        return $this;
    }

    /**
     * Returns CSS class to use for the active 'li' element when rendering.
     *
     * @throws void
     */
    public function getLiActiveClass(): string
    {
        return $this->liActiveClass;
    }

    /**
     * Normalizes given render options.
     *
     * @param array<string, bool|int|string|null> $options [optional] options for controlling rendering
     * @phpstan-param array{indent?: int|string|null, ulClass?: string|null, liClass?: string|null, minDepth?: int|null, maxDepth?: int|null, onlyActiveBranch?: bool, renderParents?: bool, escapeLabels?: bool, addClassToListItem?: bool, liActiveClass?: string|null} $options
     *
     * @return array<string, bool|int|string|null>
     * @phpstan-return array{indent: string, ulClass: string, liClass: string, minDepth: int|null, maxDepth: int|null, onlyActiveBranch: bool, renderParents: bool, escapeLabels: bool, addClassToListItem: bool, liActiveClass: string}
     *
     * @throws void
     */
    private function normalizeOptions(array $options = []): array
    {
        if (isset($options['indent'])) {
            assert(is_int($options['indent']) || is_string($options['indent']));
            $options['indent'] = $this->getWhitespace($options['indent']);
        } else {
            $options['indent'] = $this->getIndent();
        }

        if (!array_key_exists('ulClass', $options) || $options['ulClass'] === null) {
            $options['ulClass'] = $this->getUlClass();
        }

        if (!array_key_exists('liClass', $options) || $options['liClass'] === null) {
            $options['liClass'] = $this->getLiClass();
        }

        if (!array_key_exists('minDepth', $options) || $options['minDepth'] === null) {
            $options['minDepth'] = $this->getMinDepth();
        }

        if ($options['minDepth'] < 0 || $options['minDepth'] === null) {
            $options['minDepth'] = 0;
        }

        if (!array_key_exists('maxDepth', $options) || $options['maxDepth'] === null) {
            $options['maxDepth'] = $this->getMaxDepth();
        }

        if (!array_key_exists('onlyActiveBranch', $options)) {
            $options['onlyActiveBranch'] = $this->getOnlyActiveBranch();
        }

        if (!array_key_exists('escapeLabels', $options)) {
            $options['escapeLabels'] = $this->getEscapeLabels();
        }

        if (!array_key_exists('renderParents', $options)) {
            $options['renderParents'] = $this->getRenderParents();
        }

        if (!array_key_exists('addClassToListItem', $options)) {
            $options['addClassToListItem'] = $this->getAddClassToListItem();
        }

        if (!array_key_exists('liActiveClass', $options) || $options['liActiveClass'] === null) {
            $options['liActiveClass'] = $this->getLiActiveClass();
        }

        return $options;
    }

    /**
     * Render a partial with the given "model".
     *
     * @param array<mixed>                                  $params
     * @param ContainerInterface<PageInterface>|string|null $container
     * @param array<int, string>|ModelInterface|string|null $partial
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     * @throws InvalidArgumentException
     */
    private function renderPartialModel(
        array $params,
        ContainerInterface | string | null $container,
        array | ModelInterface | string | null $partial,
    ): string {
        if ($partial === null) {
            $partial = $this->getPartial();
        }

        if ($partial === null || $partial === '' || $partial === []) {
            throw new Exception\RuntimeException(
                'Unable to render menu: No partial view script provided',
            );
        }

        if (is_array($partial)) {
            if (count($partial) !== 2) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render menu: A view partial supplied as '
                    . 'an array must contain one value: the partial view script',
                );
            }

            $partial = $partial[0];
        }

        $container = $this->containerParser->parseContainer($container);

        if ($container === null) {
            $container = $this->getContainer();
        }

        return $this->renderer->render(
            $partial,
            array_merge($params, ['container' => $container]),
        );
    }

    /**
     * @param array<string, int|PageInterface|null> $found
     * @phpstan-param array{page?: PageInterface|null, depth?: int|null} $found
     *
     * @throws void
     */
    private function isActiveBranch(array $found, PageInterface $page, int | null $maxDepth): bool
    {
        if (!array_key_exists('page', $found) || !($found['page'] instanceof PageInterface)) {
            return false;
        }

        $foundPage  = $found['page'];
        $foundDepth = $found['depth'] ?? 0;

        $accept = false;

        if ($foundPage->hasPage($page)) {
            // accept if page is a direct child of the active page
            $accept = true;
        } elseif (
            $foundPage->getParent() instanceof ContainerInterface
            && $foundPage->getParent()->hasPage($page)
        ) {
            // page is a sibling of the active page...
            if (
                !$foundPage->hasPages(!$this->renderInvisible)
                || is_int($maxDepth) && $foundDepth + 1 > $maxDepth
            ) {
                // accept if active page has no children, or the
                // children are too deep to be rendered
                $accept = true;
            }
        }

        return $accept;
    }
}
