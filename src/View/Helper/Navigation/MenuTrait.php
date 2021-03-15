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

use Laminas\Log\Logger;
use Laminas\View\Exception;
use Laminas\View\Helper\EscapeHtmlAttr;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Page\PageInterface;

trait MenuTrait
{
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

    /** @var LaminasViewRenderer */
    private $renderer;

    /** @var EscapeHtmlAttr */
    private $escaper;

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
     * Implements {@link ViewHelperInterface::render()}.
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

        return $this->renderMenu($container);
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
     * @param ContainerInterface|string|null $container [optional] container to pass to view
     *                                                  script. Default is to use the container registered in the helper.
     * @param array|string|null              $partial   [optional] partial view script to use.
     *                                                  Default is to use the partial registered in the helper. If an array
     *                                                  is given, the first value is used for the partial view script.
     * @param array                          $params
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
     * @throws Exception\RuntimeException         if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
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

        if (null === $partial || '' === $partial || [] === $partial) {
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

    /**
     * @param array                                 $found
     * @param \Mezzio\Navigation\Page\PageInterface $page
     * @param int|null                              $maxDepth
     *
     * @return bool
     */
    private function isActiveBranch(array $found, PageInterface $page, ?int $maxDepth): bool
    {
        if ($found) {
            $foundPage  = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage  = null;
            $foundDepth = 0;
        }

        if (!$foundPage) {
            return false;
        }

        $accept = false;

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

        return $accept;
    }
}
