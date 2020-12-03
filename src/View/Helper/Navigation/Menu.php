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

use Laminas\Log\Logger;
use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\Helper\EscapeHtmlAttr;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\Page\PageInterface;
use RecursiveIteratorIterator;

/**
 * Helper for rendering menus from navigation containers.
 */
final class Menu extends AbstractHtmlElement implements MenuInterface
{
    use HelperTrait;

    /**
     * Whether page class should be applied to <li> element.
     *
     * @var bool
     */
    private $addClassToListItem = false;

    /**
     * Whether labels should be escaped.
     *
     * @var bool
     */
    private $escapeLabels = true;

    /**
     * Whether only active branch should be rendered.
     *
     * @var bool
     */
    private $onlyActiveBranch = false;

    /**
     * Partial view script to use for rendering menu.
     *
     * @var array|string|null
     */
    private $partial;

    /**
     * Whether parents should be rendered when only rendering active branch.
     *
     * @var bool
     */
    private $renderParents = true;

    /**
     * CSS class to use for the ul element.
     *
     * @var string
     */
    private $ulClass = 'navigation';

    /**
     * CSS class to use for the li elements.
     *
     * @var string
     */
    private $liClass = '';

    /**
     * CSS class to use for the active li element.
     *
     * @var string
     */
    private $liActiveClass = 'active';

    /** @var EscapeHtmlAttr */
    private $escaper;

    /** @var LaminasViewRenderer */
    private $renderer;

    /**
     * @param \Interop\Container\ContainerInterface $serviceLocator
     * @param Logger                                $logger
     * @param HtmlifyInterface                      $htmlify
     * @param ContainerParserInterface              $containerParser
     * @param EscapeHtmlAttr                        $escaper
     * @param LaminasViewRenderer                   $renderer
     */
    public function __construct(
        \Interop\Container\ContainerInterface $serviceLocator,
        Logger $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        EscapeHtmlAttr $escaper,
        LaminasViewRenderer $renderer
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;
        $this->escaper         = $escaper;
        $this->renderer        = $renderer;
    }

    /**
     * Renders menu.
     *
     * Implements {@link HelperInterface::render()}.
     *
     * If a partial view is registered in the helper, the menu will be rendered
     * using the given partial script. If no partial is registered, the menu
     * will be rendered as an 'ul' element by the helper's internal method.
     *
     * @see renderPartial()
     * @see renderMenu()
     *
     * @param ContainerInterface|string|null $container [optional] container to render.
     *                                                  Default is null, which indicates
     *                                                  that the helper should render
     *                                                  the container returned by {@link getContainer()}.
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function render($container = null): string
    {
        $partial = $this->getPartial();

        if ($partial) {
            return $this->renderPartial($container, $partial);
        }

        return $this->renderMenu($container);
    }

    /**
     * Renders the deepest active menu within [$minDepth, $maxDepth], (called from {@link renderMenu()}).
     *
     * @param ContainerInterface $container          container to render
     * @param string             $ulClass            CSS class for first UL
     * @param string             $liCssClass         CSS class for all LI
     * @param string             $indent             initial indentation
     * @param int|null           $minDepth           minimum depth
     * @param int|null           $maxDepth           maximum depth
     * @param bool               $escapeLabels       Whether or not to escape the labels
     * @param bool               $addClassToListItem Whether or not page class applied to <li> element
     * @param string             $liActiveClass      CSS class for active LI
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return string
     */
    private function renderDeepestMenu(
        ContainerInterface $container,
        string $ulClass,
        string $liCssClass,
        string $indent,
        ?int $minDepth,
        ?int $maxDepth,
        bool $escapeLabels,
        bool $addClassToListItem,
        string $liActiveClass
    ): string {
        if (!$active = $this->findActive($container, $minDepth - 1, $maxDepth)) {
            return '';
        }

        // special case if active page is one below minDepth
        if ($active['depth'] < $minDepth) {
            if (!$active['page']->hasPages(!$this->renderInvisible)) {
                return '';
            }
        } elseif (!$active['page']->hasPages(!$this->renderInvisible)) {
            // found pages has no children; render siblings
            $active['page'] = $active['page']->getParent();
        } elseif (is_int($maxDepth) && $active['depth'] + 1 > $maxDepth) {
            // children are below max depth; render siblings
            $active['page'] = $active['page']->getParent();
        }

        $ulClass = $ulClass ? ' class="' . ($this->escaper)($ulClass) . '"' : '';
        $html    = $indent . '<ul' . $ulClass . '>' . PHP_EOL;

        foreach ($active['page'] as $subPage) {
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

            $liClass = empty($liClasses) ? '' : ' class="' . ($this->escaper)(implode(' ', $liClasses)) . '"';
            $html .= $indent . '    <li' . $liClass . '>' . PHP_EOL;
            $html .= $indent . '        ' . $this->htmlify->toHtml(self::class, $subPage, $escapeLabels, $addClassToListItem) . PHP_EOL;
            $html .= $indent . '    </li>' . PHP_EOL;
        }

        $html .= $indent . '</ul>';

        return $html;
    }

    /**
     * Renders helper.
     *
     * Renders a HTML 'ul' for the given $container. If $container is not given,
     * the container registered in the helper will be used.
     *
     * Available $options:
     *
     * @param ContainerInterface|string|null $container [optional] container to create menu from.
     *                                                  Default is to use the container retrieved from {@link getContainer()}.
     * @param array                          $options   [optional] options for controlling rendering
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderMenu($container = null, array $options = []): string
    {
        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        $options = $this->normalizeOptions($options);

        if ($options['onlyActiveBranch'] && !$options['renderParents']) {
            return $this->renderDeepestMenu(
                $container,
                $options['ulClass'],
                $options['liClass'],
                $options['indent'],
                $options['minDepth'],
                $options['maxDepth'],
                $options['escapeLabels'],
                $options['addClassToListItem'],
                $options['liActiveClass']
            );
        }

        return $this->renderNormalMenu(
            $container,
            $options['ulClass'],
            $options['liClass'],
            $options['indent'],
            $options['minDepth'],
            $options['maxDepth'],
            $options['onlyActiveBranch'],
            $options['escapeLabels'],
            $options['addClassToListItem'],
            $options['liActiveClass']
        );
    }

    /**
     * Renders a normal menu (called from {@link renderMenu()}).
     *
     * @param ContainerInterface $container          container to render
     * @param string             $ulClass            CSS class for first UL
     * @param string             $liCssClass         CSS class for all LI
     * @param string             $indent             initial indentation
     * @param int|null           $minDepth           minimum depth
     * @param int|null           $maxDepth           maximum depth
     * @param bool               $onlyActive         render only active branch?
     * @param bool               $escapeLabels       Whether or not to escape the labels
     * @param bool               $addClassToListItem Whether or not page class applied to <li> element
     * @param string             $liActiveClass      CSS class for active LI
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return string
     */
    private function renderNormalMenu(
        ContainerInterface $container,
        string $ulClass,
        string $liCssClass,
        string $indent,
        ?int $minDepth,
        ?int $maxDepth,
        bool $onlyActive,
        bool $escapeLabels,
        bool $addClassToListItem,
        string $liActiveClass
    ): string {
        $html = '';

        // find deepest active
        $found = $this->findActive($container, $minDepth, $maxDepth);

        if ($found) {
            $foundPage  = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage  = null;
            $foundDepth = 0;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator(
            $container,
            RecursiveIteratorIterator::SELF_FIRST
        );

        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach ($iterator as $page) {
            $depth    = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibility
                continue;
            }

            if ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } elseif ($foundPage->getParent()->hasPage($page)) {
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
                }

                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);
            if ($depth > $prevDepth) {
                // start new ul tag
                if ($ulClass && 0 === $depth) {
                    $ulClass = ' class="' . ($this->escaper)($ulClass) . '"';
                } else {
                    $ulClass = '';
                }

                $html .= $myIndent . '<ul' . $ulClass . '>' . PHP_EOL;
            } elseif ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; --$i) {
                    $ind = $indent . str_repeat('        ', $i);
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

            $liClass = empty($liClasses) ? '' : ' class="' . ($this->escaper)(implode(' ', $liClasses)) . '"';
            $html .= $myIndent . '    <li' . $liClass . '>' . PHP_EOL
                . $myIndent . '        ' . $this->htmlify->toHtml(self::class, $page, $escapeLabels, $addClassToListItem) . PHP_EOL;

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth + 1; 0 < $i; --$i) {
                $myIndent = $indent . str_repeat('        ', $i - 1);
                $html .= $myIndent . '    </li>' . PHP_EOL
                    . $myIndent . '</ul>' . PHP_EOL;
            }

            $html = rtrim($html, PHP_EOL);
        }

        return $html;
    }

    /**
     * Renders the given $container by invoking the partial view helper.
     *
     * The container will simply be passed on as a model to the view script
     * as-is, and will be available in the partial script as 'container', e.g.
     * <code>echo 'Number of pages: ', count($this->container);</code>.
     *
     * @param ContainerInterface|string|null $container [optional] container to pass to view
     *                                                  script. Default is to use the container registered in the helper.
     * @param array|string|null              $partial   [optional] partial view script to use.
     *                                                  Default is to use the partial registered in the helper. If an array
     *                                                  is given, the first value is used for the partial view script.
     *
     * @throws Exception\RuntimeException                            if no partial provided
     * @throws Exception\InvalidArgumentException                    if partial is invalid array
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
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
     * @param ContainerInterface|string|null $container [optional] container to pass to view
     *                                                  script. Default is to use the container registered in the helper.
     * @param array|string|null              $partial   [optional] partial view script to use.
     *                                                  Default is to use the partial registered in the helper. If an array
     *                                                  is given, the first value is used for the partial view script.
     * @param array                          $params
     *
     * @throws Exception\RuntimeException                            if no partial provided
     * @throws Exception\InvalidArgumentException                    if partial is invalid array
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderPartialWithParams(array $params = [], $container = null, $partial = null): string
    {
        return $this->renderPartialModel($params, $container, $partial);
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
     * @param ContainerInterface|null $container     [optional] container to render.
     *                                               Default is to render the container registered in the helper.
     * @param string|null             $ulClass       [optional] CSS class to use for UL element.
     *                                               Default is to use the value from {@link getUlClass()}.
     * @param string|null             $liClass       [optional] CSS class to use for LI elements.
     *                                               Default is to use the value from {@link getLiClass()}.
     * @param int|string|null         $indent        [optional] indentation as a string or number
     *                                               of spaces. Default is to use the value retrieved from
     *                                               {@link getIndent()}.
     * @param string|null             $liActiveClass [optional] CSS class to use for UL
     *                                               element. Default is to use the value from {@link getUlClass()}.
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function renderSubMenu(
        ?ContainerInterface $container = null,
        ?string $ulClass = null,
        ?string $liClass = null,
        $indent = null,
        ?string $liActiveClass = null
    ): string {
        return $this->renderMenu(
            $container,
            [
                'indent' => $indent,
                'ulClass' => $ulClass,
                'liClass' => $liClass,
                'minDepth' => null,
                'maxDepth' => null,
                'onlyActiveBranch' => true,
                'renderParents' => false,
                'escapeLabels' => true,
                'addClassToListItem' => false,
                'liActiveClass' => $liActiveClass,
            ]
        );
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty.
     *
     * Overrides {@link AbstractHelper::htmlify()}.
     *
     * @param PageInterface $page               page to generate HTML for
     * @param bool          $escapeLabel        Whether or not to escape the label
     * @param bool          $addClassToListItem Whether or not to add the page class to the list item
     *
     * @return string
     */
    public function htmlify(PageInterface $page, bool $escapeLabel = true, bool $addClassToListItem = false): string
    {
        return $this->htmlify->toHtml(self::class, $page, $escapeLabel, $addClassToListItem);
    }

    /**
     * Normalizes given render options.
     *
     * @param array $options [optional] options to normalize
     *
     * @return array
     */
    private function normalizeOptions(array $options = []): array
    {
        if (isset($options['indent'])) {
            $options['indent'] = $this->getWhitespace($options['indent']);
        } else {
            $options['indent'] = $this->getIndent();
        }

        if (isset($options['ulClass']) && null !== $options['ulClass']) {
            $options['ulClass'] = (string) $options['ulClass'];
        } else {
            $options['ulClass'] = $this->getUlClass();
        }

        if (isset($options['liClass']) && null !== $options['liClass']) {
            $options['liClass'] = (string) $options['liClass'];
        } else {
            $options['liClass'] = $this->getLiClass();
        }

        if (array_key_exists('minDepth', $options)) {
            if (null !== $options['minDepth']) {
                $options['minDepth'] = (int) $options['minDepth'];
            }
        } else {
            $options['minDepth'] = $this->getMinDepth();
        }

        if (0 > $options['minDepth'] || null === $options['minDepth']) {
            $options['minDepth'] = 0;
        }

        if (array_key_exists('maxDepth', $options)) {
            if (null !== $options['maxDepth']) {
                $options['maxDepth'] = (int) $options['maxDepth'];
            }
        } else {
            $options['maxDepth'] = $this->getMaxDepth();
        }

        if (!isset($options['onlyActiveBranch'])) {
            $options['onlyActiveBranch'] = $this->getOnlyActiveBranch();
        }

        if (!isset($options['escapeLabels'])) {
            $options['escapeLabels'] = $this->escapeLabels;
        }

        if (!isset($options['renderParents'])) {
            $options['renderParents'] = $this->getRenderParents();
        }

        if (!isset($options['addClassToListItem'])) {
            $options['addClassToListItem'] = $this->getAddClassToListItem();
        }

        if (isset($options['liActiveClass']) && null !== $options['liActiveClass']) {
            $options['liActiveClass'] = (string) $options['liActiveClass'];
        } else {
            $options['liActiveClass'] = $this->getLiActiveClass();
        }

        return $options;
    }

    /**
     * Sets a flag indicating whether labels should be escaped.
     *
     * @param bool $flag [optional] escape labels
     *
     * @return self
     */
    public function escapeLabels(bool $flag = true): self
    {
        $this->escapeLabels = $flag;

        return $this;
    }

    /**
     * @return bool
     */
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
     * @return self fluent interface, returns self
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
     * @return self
     */
    public function setOnlyActiveBranch(bool $flag = true): self
    {
        $this->onlyActiveBranch = $flag;

        return $this;
    }

    /**
     * Returns a flag indicating whether only active branch should be rendered.
     *
     * By default, this value is false, meaning the entire menu will be
     * be rendered.
     *
     * @return bool
     */
    public function getOnlyActiveBranch(): bool
    {
        return $this->onlyActiveBranch;
    }

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array|string|null $partial partial view script or null. If an array
     *                                   is given, the first value is used for the partial view script.
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
     * Enables/disables rendering of parents when only rendering active branch.
     *
     * See {@link setOnlyActiveBranch()} for more information.
     *
     * @param bool $flag [optional] render parents when rendering active branch
     *
     * @return self
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
     * @return bool
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
     * @return self
     */
    public function setUlClass(string $ulClass): self
    {
        $this->ulClass = $ulClass;

        return $this;
    }

    /**
     * Returns CSS class to use for the first 'ul' element when rendering.
     *
     * @return string
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
     * @return self
     */
    public function setLiClass(string $liClass): self
    {
        $this->liClass = $liClass;

        return $this;
    }

    /**
     * Returns CSS class to use for the 'li' elements when rendering.
     *
     * @return string
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
     * @return self
     */
    public function setLiActiveClass(string $liActiveClass): self
    {
        $this->liActiveClass = $liActiveClass;

        return $this;
    }

    /**
     * Returns CSS class to use for the active 'li' element when rendering.
     *
     * @return string
     */
    public function getLiActiveClass(): string
    {
        return $this->liActiveClass;
    }

    /**
     * Render a partial with the given "model".
     *
     * @param array                          $params
     * @param ContainerInterface|string|null $container
     * @param array|string|null              $partial
     *
     * @throws Exception\RuntimeException                            if no partial provided
     * @throws Exception\InvalidArgumentException                    if partial is invalid array
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return string
     */
    private function renderPartialModel(array $params, $container, $partial): string
    {
        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        if (null === $partial) {
            $partial = $this->getPartial();
        }

        if (empty($partial)) {
            throw new Exception\RuntimeException(
                'Unable to render menu: No partial view script provided'
            );
        }

        if (is_array($partial)) {
            if (2 !== count($partial)) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render menu: A view partial supplied as '
                    . 'an array must contain one value: the partial view script'
                );
            }

            $partial = $partial[0];
        }

        $model = array_merge($params, ['container' => $container]);

        return $this->renderer->render($partial, $model);
    }
}
