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

use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHtmlElement;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Page\PageInterface;
use RecursiveIteratorIterator;

use function assert;
use function get_class;
use function gettype;
use function implode;
use function is_bool;
use function is_int;
use function is_object;
use function is_string;
use function rtrim;
use function sprintf;
use function str_repeat;

use const PHP_EOL;

/**
 * Helper for rendering menus from navigation containers.
 */
final class Menu extends AbstractHtmlElement implements MenuInterface
{
    use HelperTrait, MenuTrait {
        MenuTrait::htmlify insteadof HelperTrait;
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
     * @param array<mixed>                   $options   [optional] options for controlling rendering
     *
     * @throws Exception\InvalidArgumentException
     */
    public function renderMenu($container = null, array $options = []): string
    {
        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        $options = $this->normalizeOptions($options);

        assert(is_string($options['ulClass']));
        assert(is_string($options['liClass']));
        assert(is_string($options['indent']));
        assert(is_int($options['minDepth']));
        assert(is_int($options['maxDepth']) || null === $options['maxDepth']);
        assert(is_bool($options['onlyActiveBranch']));
        assert(is_bool($options['escapeLabels']));
        assert(is_bool($options['addClassToListItem']));
        assert(is_string($options['liActiveClass']));

        if ($options['onlyActiveBranch'] && !$options['renderParents']) {
            return $this->renderDeepestMenu(
                $container,
                $options['ulClass'],
                $options['liClass'],
                $options['indent'],
                $options['minDepth'] ?? 0,
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
     * @throws Exception\InvalidArgumentException
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
     * Renders the deepest active menu within [$minDepth, $maxDepth], (called from {@link renderMenu()}).
     *
     * @param ContainerInterface $container          container to render
     * @param string             $ulClass            CSS class for first UL
     * @param string             $liCssClass         CSS class for all LI
     * @param string             $indent             initial indentation
     * @param int                $minDepth           minimum depth
     * @param int|null           $maxDepth           maximum depth
     * @param bool               $escapeLabels       Whether or not to escape the labels
     * @param bool               $addClassToListItem Whether or not page class applied to <li> element
     * @param string             $liActiveClass      CSS class for active LI
     *
     * @throws Exception\InvalidArgumentException
     */
    private function renderDeepestMenu(
        ContainerInterface $container,
        string $ulClass,
        string $liCssClass,
        string $indent,
        int $minDepth,
        ?int $maxDepth,
        bool $escapeLabels,
        bool $addClassToListItem,
        string $liActiveClass
    ): string {
        $active = $this->findActive($container, $minDepth - 1, $maxDepth);

        if (!$active) {
            return '';
        }

        assert(
            $active['page'] instanceof PageInterface,
            sprintf(
                '$active[\'page\'] should be an Instance of %s, but was %s',
                PageInterface::class,
                is_object($active['page']) ? get_class($active['page']) : gettype($active['page'])
            )
        );

        assert(is_int($active['depth']));

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

        assert(
            $active['page'] instanceof ContainerInterface,
            sprintf(
                '$active[\'page\'] should be an Instance of %s, but was %s',
                ContainerInterface::class,
                is_object($active['page']) ? get_class($active['page']) : gettype($active['page'])
            )
        );

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

            $liClass = [] === $liClasses ? '' : ' class="' . ($this->escaper)(implode(' ', $liClasses)) . '"';
            $html   .= $indent . '    <li' . $liClass . '>' . PHP_EOL;
            $html   .= $indent . '        ' . $this->htmlify->toHtml(self::class, $subPage, $escapeLabels, $addClassToListItem) . PHP_EOL;
            $html   .= $indent . '    </li>' . PHP_EOL;
        }

        $html .= $indent . '</ul>';

        return $html;
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
     * @throws Exception\InvalidArgumentException
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
            assert(
                $page instanceof PageInterface,
                sprintf(
                    '$page should be an Instance of %s, but was %s',
                    PageInterface::class,
                    is_object($page) ? get_class($page) : gettype($page)
                )
            );

            $depth = $iterator->getDepth();

            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibility
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
                if ($ulClass && 0 === $depth) {
                    $ulClass = ' class="' . ($this->escaper)($ulClass) . '"';
                } else {
                    $ulClass = '';
                }

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

            $liClass = [] === $liClasses ? '' : ' class="' . ($this->escaper)(implode(' ', $liClasses)) . '"';
            $html   .= $myIndent . '    <li' . $liClass . '>' . PHP_EOL
                . $myIndent . '        ' . $this->htmlify->toHtml(self::class, $page, $escapeLabels, $addClassToListItem) . PHP_EOL;

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
