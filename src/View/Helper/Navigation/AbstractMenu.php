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
use Override;
use RecursiveIteratorIterator;

use function array_key_exists;
use function array_merge;
use function assert;
use function count;
use function get_debug_type;
use function implode;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function rtrim;
use function sprintf;
use function str_repeat;

use const PHP_EOL;

/**
 * Helper for rendering menus from navigation containers.
 *
 * phpcs:disable SlevomatCodingStandard.Classes.TraitUseDeclaration.MultipleTraitsPerDeclaration
 */
abstract class AbstractMenu extends AbstractHelper implements MenuInterface
{
    /**
     * Whether page class should be applied to <li> element.
     */
    protected bool $addClassToListItem = false;

    /**
     * Whether labels should be escaped.
     */
    protected bool $escapeLabels = true;

    /**
     * Whether only active branch should be rendered.
     */
    protected bool $onlyActiveBranch = false;

    /**
     * Partial view script to use for rendering menu.
     *
     * @var array<int, string>|ModelInterface|string|null
     */
    protected array | ModelInterface | string | null $partial = null;

    /**
     * Whether parents should be rendered when only rendering active branch.
     */
    protected bool $renderParents = true;

    /**
     * CSS class to use for the ul element.
     */
    protected string $ulClass = 'navigation';

    /**
     * CSS class to use for the li elements.
     */
    protected string $liClass = '';

    /**
     * CSS class to use for the active li element.
     */
    protected string $liActiveClass = 'active';

    /** @throws void */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        protected EscapeHtmlAttr $escaper,
        private readonly PartialRendererInterface $renderer,
    ) {
        parent::__construct($serviceLocator, $htmlify, $containerParser);
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
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to render. Default is null, which indicates that the helper should render the container returned by {@link getContainer()}.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    #[Override]
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
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to pass to view script. Default is to use the container registered in the helper.
     * @param array<int, string>|ModelInterface|string|null $partial   [optional] partial view script to use. Default is to use the partial registered in the helper. If an array is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     */
    #[Override]
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
     * @param array<int|string, mixed>                      $params
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to pass to view script. Default is to use the container registered in the helper.
     * @param array<int, string>|string|null                $partial   [optional] partial view script to use. Default is to use the partial registered in the helper. If an array is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     */
    #[Override]
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
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public function htmlify(PageInterface $page, bool $escapeLabel = true, bool $addClassToListItem = false): string
    {
        try {
            return $this->htmlify->toHtml(static::class, $page, $escapeLabel, $addClassToListItem);
        } catch (RuntimeException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Sets a flag indicating whether labels should be escaped.
     *
     * @param bool $flag [optional] escape labels
     *
     * @throws void
     */
    #[Override]
    public function escapeLabels(bool $flag = true): static
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
     * @return static fluent interface
     *
     * @throws void
     */
    #[Override]
    public function setAddClassToListItem(bool $flag = true): static
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
    #[Override]
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
    #[Override]
    public function setOnlyActiveBranch(bool $flag = true): static
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
    #[Override]
    public function getOnlyActiveBranch(): bool
    {
        return $this->onlyActiveBranch;
    }

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array<int, string>|ModelInterface|string|null $partial partial view script or null. If an array is given, the first value is used for the partial view script.
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    #[Override]
    public function setPartial($partial): static
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
    #[Override]
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
    #[Override]
    public function setRenderParents(bool $flag = true): static
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
    #[Override]
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
    #[Override]
    public function setUlClass(string $ulClass): static
    {
        $this->ulClass = $ulClass;

        return $this;
    }

    /**
     * Returns CSS class to use for the first 'ul' element when rendering.
     *
     * @throws void
     */
    #[Override]
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
    #[Override]
    public function setLiClass(string $liClass): static
    {
        $this->liClass = $liClass;

        return $this;
    }

    /**
     * Returns CSS class to use for the 'li' elements when rendering.
     *
     * @throws void
     */
    #[Override]
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
    #[Override]
    public function setLiActiveClass(string $liActiveClass): static
    {
        $this->liActiveClass = $liActiveClass;

        return $this;
    }

    /**
     * Returns CSS class to use for the active 'li' element when rendering.
     *
     * @throws void
     */
    #[Override]
    public function getLiActiveClass(): string
    {
        return $this->liActiveClass;
    }

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
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public function renderMenu(ContainerInterface | string | null $container = null, array $options = []): string
    {
        try {
            $container = $this->containerParser->parseContainer($container);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        if ($container === null) {
            $container = $this->getContainer();
        }

        $options = $this->normalizeOptions($options);

        assert(is_string($options['ulClass']));
        assert(is_string($options['liClass']));
        assert(is_string($options['indent']));
        assert(is_int($options['minDepth']));
        assert(is_bool($options['onlyActiveBranch']));
        assert(is_bool($options['renderParents']));
        assert(is_bool($options['escapeLabels']));
        assert(is_bool($options['addClassToListItem']));
        assert(is_string($options['liActiveClass']));

        assert($container instanceof ContainerInterface);

        if ($options['onlyActiveBranch'] && !$options['renderParents']) {
            return $this->renderDeepestMenu(
                container: $container,
                ulClass: $options['ulClass'],
                liCssClass: $options['liClass'],
                indent: $options['indent'],
                minDepth: $options['minDepth'],
                maxDepth: $options['maxDepth'],
                escapeLabels: $options['escapeLabels'],
                addClassToListItem: $options['addClassToListItem'],
                liActiveClass: $options['liActiveClass'],
            );
        }

        return $this->renderNormalMenu(
            container: $container,
            ulClass: $options['ulClass'],
            liCssClass: $options['liClass'],
            indent: $options['indent'],
            minDepth: $options['minDepth'],
            maxDepth: $options['maxDepth'],
            onlyActive: $options['onlyActiveBranch'],
            escapeLabels: $options['escapeLabels'],
            addClassToListItem: $options['addClassToListItem'],
            liActiveClass: $options['liActiveClass'],
        );
    }

    /**
     * Renders the inner-most sub menu for the active page in the $container.
     *
     * This is a convenience method which is equivalent to the following call:
     * <code>
     * renderMenu($container, array(
     *     'indent'           => $indent,
     *     'ulClass'          => $ulClass,
     *     'liClass'          => $liClass,
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
     * @param string|null                            $liActiveClass [optional] CSS class to use for UL
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public function renderSubMenu(
        ContainerInterface | null $container = null,
        string | null $ulClass = null,
        string | null $liClass = null,
        int | string | null $indent = null,
        string | null $liActiveClass = null,
    ): string {
        $this->setMaxDepth(null);
        $this->setMinDepth(null);
        $this->setRenderParents(false);
        $this->setAddClassToListItem(false);

        return $this->renderMenu(
            $container,
            [
                'indent' => $indent,
                'ulClass' => $ulClass,
                'liClass' => $liClass,
                'onlyActiveBranch' => true,
                'escapeLabels' => true,
                'liActiveClass' => $liActiveClass,
            ],
        );
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
    protected function normalizeOptions(array $options = []): array
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
     * @param array<string, int|PageInterface|null> $found
     * @phpstan-param array{page?: PageInterface|null, depth?: int|null} $found
     *
     * @throws void
     */
    protected function isActiveBranch(array $found, PageInterface $page, int | null $maxDepth): bool
    {
        if (!array_key_exists('page', $found) || !($found['page'] instanceof PageInterface)) {
            return false;
        }

        $foundPage  = $found['page'];
        $foundDepth = $found['depth'] ?? 0;

        if ($foundPage->hasPage($page)) {
            // accept if page is a direct child of the active page
            return true;
        }

        if (
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
                return true;
            }
        }

        return false;
    }

    /**
     * Render a partial with the given "model".
     *
     * @param array<int|string, mixed>                      $params
     * @param ContainerInterface<PageInterface>|string|null $container
     * @param array<int, string>|ModelInterface|string|null $partial
     *
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
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

        try {
            $container = $this->containerParser->parseContainer($container);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        if ($container === null) {
            $container = $this->getContainer();
        }

        return $this->renderer->render(
            $partial,
            array_merge($params, ['container' => $container]),
        );
    }

    /**
     * Renders the deepest active menu within [$minDepth, $maxDepth], (called from {@link renderMenu()}).
     *
     * @param ContainerInterface<PageInterface> $container          container to render
     * @param string                            $ulClass            CSS class for first UL
     * @param string                            $liCssClass         CSS class for all LI
     * @param string                            $indent             initial indentation
     * @param int                               $minDepth           minimum depth
     * @param int|null                          $maxDepth           maximum depth
     * @param bool                              $escapeLabels       Whether or not to escape the labels
     * @param bool                              $addClassToListItem Whether or not page class applied to <li> element
     * @param string                            $liActiveClass      CSS class for active LI
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    private function renderDeepestMenu(
        ContainerInterface $container,
        string $ulClass,
        string $liCssClass,
        string $indent,
        int $minDepth,
        int | null $maxDepth,
        bool $escapeLabels,
        bool $addClassToListItem,
        string $liActiveClass,
    ): string {
        $active = $this->findActive($container, $minDepth - 1, $maxDepth);

        if (!array_key_exists('page', $active) || !($active['page'] instanceof PageInterface)) {
            return '';
        }

        $activePage = $active['page'];

        // special case if active page is one below minDepth
        if (!array_key_exists('depth', $active) || $active['depth'] < $minDepth) {
            if (!$activePage->hasPages(!$this->renderInvisible)) {
                return '';
            }
        } elseif (!$active['page']->hasPages(!$this->renderInvisible)) {
            // found pages has no children; render siblings
            $activePage = $active['page']->getParent();
        } elseif (is_int($maxDepth) && $active['depth'] + 1 > $maxDepth) {
            // children are below max depth; render siblings
            $activePage = $active['page']->getParent();
        }

        $ulClass = $ulClass ? ' class="' . ($this->escaper)($ulClass) . '"' : '';
        $html    = $indent . '<ul' . $ulClass . '>' . PHP_EOL;

        assert(
            $activePage instanceof ContainerInterface,
            sprintf(
                '$activePage should be an Instance of %s, but was %s',
                ContainerInterface::class,
                get_debug_type($activePage),
            ),
        );

        foreach ($activePage as $subPage) {
            if (!$this->accept($subPage)) {
                continue;
            }

            // render li tag and page
            $liClasses = [];

            // Is page active?
            if ($subPage->isActive(true)) {
                $liClasses[] = $liActiveClass;
            }

            if ($liCssClass) {
                $liClasses[] = $liCssClass;
            }

            if ($subPage->getLiClass()) {
                $liClasses[] = $subPage->getLiClass();
            }

            // Add CSS class from page to <li>
            if ($addClassToListItem && $subPage->getClass()) {
                $liClasses[] = $subPage->getClass();
            }

            $liClass = $liClasses === []
                ? ''
                : ' class="' . ($this->escaper)(implode(' ', $liClasses)) . '"';
            $html   .= $indent . '    <li' . $liClass . '>' . PHP_EOL;

            try {
                $subPageHtml = $this->htmlify->toHtml(
                    static::class,
                    $subPage,
                    $escapeLabels,
                    $addClassToListItem,
                );
            } catch (RuntimeException $e) {
                throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
            }

            $html .= $indent . '        ' . $subPageHtml . PHP_EOL;
            $html .= $indent . '    </li>' . PHP_EOL;
        }

        return $html . $indent . '</ul>';
    }

    /**
     * Renders a normal menu (called from {@link renderMenu()}).
     *
     * @param ContainerInterface<PageInterface> $container          container to render
     * @param string                            $ulClass            CSS class for first UL
     * @param string                            $liCssClass         CSS class for all LI
     * @param string                            $indent             initial indentation
     * @param int|null                          $minDepth           minimum depth
     * @param int|null                          $maxDepth           maximum depth
     * @param bool                              $onlyActive         render only active branch?
     * @param bool                              $escapeLabels       Whether or not to escape the labels
     * @param bool                              $addClassToListItem Whether or not page class applied to <li> element
     * @param string                            $liActiveClass      CSS class for active LI
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    private function renderNormalMenu(
        ContainerInterface $container,
        string $ulClass,
        string $liCssClass,
        string $indent,
        int | null $minDepth,
        int | null $maxDepth,
        bool $onlyActive,
        bool $escapeLabels,
        bool $addClassToListItem,
        string $liActiveClass,
    ): string {
        $html = '';

        // find deepest active
        $found = $this->findActive($container, $minDepth, $maxDepth);

        // create iterator
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;

        foreach ($iterator as $page) {
            assert(
                $page instanceof PageInterface,
                sprintf(
                    '$page should be an Instance of %s, but was %s',
                    PageInterface::class,
                    get_debug_type($page),
                ),
            );

            $depth = $iterator->getDepth();

            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by Authorization/Visibility
                continue;
            }

            $isActive = $page->isActive(true);

            if ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = $this->isActiveBranch($found, $page, $maxDepth);

                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth   -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);

            if ($depth > $prevDepth) {
                // start new ul tag
                $ulClass = $ulClass && $depth === 0 ? ' class="' . ($this->escaper)($ulClass) . '"' : '';

                $html .= $myIndent . '<ul' . $ulClass . '>' . PHP_EOL;
            } elseif ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; --$i) {
                    $ind   = $indent . str_repeat('        ', $i);
                    $html .= $ind . '    </li>' . PHP_EOL;
                    $html .= $ind . '</ul>' . PHP_EOL;
                }

                // close previous li tag
                $html .= $myIndent . '    </li>' . PHP_EOL;
            } else {
                // close previous li tag
                $html .= $myIndent . '    </li>' . PHP_EOL;
            }

            // render li tag and page
            $liClasses = [];

            // Is page active?
            if ($isActive) {
                $liClasses[] = $liActiveClass;
            }

            if ($liCssClass) {
                $liClasses[] = $liCssClass;
            }

            if ($page->getLiClass()) {
                $liClasses[] = $page->getLiClass();
            }

            // Add CSS class from page to <li>
            if ($addClassToListItem && $page->getClass()) {
                $liClasses[] = $page->getClass();
            }

            $liClass = $liClasses === []
                ? ''
                : ' class="' . ($this->escaper)(implode(' ', $liClasses)) . '"';

            try {
                $pageHtml = $this->htmlify->toHtml(
                    static::class,
                    $page,
                    $escapeLabels,
                    $addClassToListItem,
                );
            } catch (RuntimeException $e) {
                throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
            }

            $html .= $myIndent . '    <li' . $liClass . '>' . PHP_EOL
                . $myIndent . '        ' . $pageHtml . PHP_EOL;

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth + 1; 0 < $i; --$i) {
                $myIndent = $indent . str_repeat('        ', $i - 1);
                $html    .= $myIndent . '    </li>' . PHP_EOL
                    . $myIndent . '</ul>' . PHP_EOL;
            }

            $html = rtrim($html, PHP_EOL);
        }

        return $html;
    }
}
