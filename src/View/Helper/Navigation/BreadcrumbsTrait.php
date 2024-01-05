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
use Laminas\I18n\View\Helper\Translate;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Laminas\View\Exception;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Model\ModelInterface;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Log\LoggerInterface;

use function array_merge;
use function array_reverse;
use function array_unshift;
use function assert;
use function count;
use function get_debug_type;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Helper for printing breadcrumbs.
 */
trait BreadcrumbsTrait
{
    /**
     * Whether last page in breadcrumb should be hyperlinked.
     */
    private bool $linkLast = false;

    /**
     * Partial view script to use for rendering menu.
     *
     * @var array<int, string>|ModelInterface|string|null
     */
    private array | ModelInterface | string | null $partial = null;

    /**
     * Breadcrumbs separator string.
     */
    private string $separator = ' &gt; ';

    /** @throws void */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        LoggerInterface $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        private EscapeHtml $escaper,
        private PartialRendererInterface $renderer,
        private Translate | null $translator = null,
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;
    }

    /**
     * Renders helper.
     *
     * Implements {@link ViewHelperInterface::render()}.
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

        return $this->renderStraight($container);
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
     * @param array<string, array<mixed>|string>            $params
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
    public function renderPartialWithParams(
        array $params = [],
        ContainerInterface | string | null $container = null,
        array | ModelInterface | string | null $partial = null,
    ): string {
        return $this->renderPartialModel($params, $container, $partial);
    }

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper.
     *
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to render. Default is
     *                                                  to render the container registered in the helper.
     *
     * @throws InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws RuntimeException
     */
    public function renderStraight(ContainerInterface | string | null $container = null): string
    {
        $container = $this->containerParser->parseContainer($container);

        if (!$container instanceof ContainerInterface) {
            $container = $this->getContainer();
        }

        // find deepest active
        $active = $this->findActive($container);

        if (!$active) {
            return '';
        }

        $active = $active['page'];

        assert(
            $active instanceof PageInterface,
            sprintf(
                '$active should be an Instance of %s, but was %s',
                PageInterface::class,
                get_debug_type($active),
            ),
        );

        $html = [];

        // put the deepest active page last in breadcrumbs
        if ($this->getLinkLast()) {
            $html[] = $this->renderBreadcrumbItem(
                $this->htmlify->toHtml(self::class, $active),
                $active->getLiClass() ?? '',
                $active->isActive(),
            );
        } else {
            $label = (string) $active->getLabel();

            if ($this->translator !== null) {
                $label = ($this->translator)($label, $active->getTextDomain());
                assert(is_string($label));
            }

            $label = ($this->escaper)($label);
            assert(is_string($label));

            $html[] = $this->renderBreadcrumbItem(
                $label,
                $active->getLiClass() ?? '',
                $active->isActive(),
            );
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof PageInterface) {
                // prepend crumb to html
                $entry = $this->renderBreadcrumbItem(
                    $this->htmlify->toHtml(self::class, $parent),
                    $parent->getLiClass() ?? '',
                    $parent->isActive(),
                );
                array_unshift($html, $entry);
            }

            if ($parent === $container) {
                // at the root of the given container
                break;
            }

            $active = $parent;
        }

        return $this->combineRendered($html);
    }

    /**
     * Sets whether last page in breadcrumbs should be hyperlinked.
     *
     * @param bool $linkLast whether last page should be hyperlinked
     *
     * @throws void
     */
    public function setLinkLast(bool $linkLast): self
    {
        $this->linkLast = $linkLast;

        return $this;
    }

    /**
     * Returns whether last page in breadcrumbs should be hyperlinked.
     *
     * @throws void
     */
    public function getLinkLast(): bool
    {
        return $this->linkLast;
    }

    /**
     * Sets which partial view script to use for rendering menu.
     *
     * @param array<int, string>|ModelInterface|string|null $partial partial view script or null. If an array is
     *                                                               given, the first value is used for the partial view script.
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
     * Sets breadcrumb separator.
     *
     * @param string $separator separator string
     *
     * @throws void
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
     *
     * @throws void
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @throws void
     */
    public function getMinDepth(): int | null
    {
        if (!is_int($this->minDepth) || 0 > $this->minDepth) {
            return 1;
        }

        return $this->minDepth;
    }

    /**
     * Render a partial with the given "model".
     *
     * @param array<string, array<mixed>|string>            $params
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
                'Unable to render breadcrumbs: No partial view script provided',
            );
        }

        if (is_array($partial)) {
            if (count($partial) !== 2) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render breadcrumbs: A view partial supplied as '
                    . 'an array must contain one value: the partial view script',
                );
            }

            $partial = $partial[0];
        }

        $container = $this->containerParser->parseContainer($container);

        if (!$container instanceof ContainerInterface) {
            $container = $this->getContainer();
        }

        /** @var array<string, array<mixed>> $model */
        $model  = array_merge($params, ['pages' => []], ['separator' => $this->getSeparator()]);
        $active = $this->findActive($container);

        if ($active !== []) {
            $active = $active['page'];

            assert(
                $active instanceof PageInterface,
                sprintf(
                    '$active should be an Instance of %s, but was %s',
                    PageInterface::class,
                    get_debug_type($active),
                ),
            );

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
}
