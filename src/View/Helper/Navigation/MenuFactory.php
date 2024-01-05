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

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function assert;
use function get_debug_type;
use function sprintf;

final class MenuFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Menu
    {
        assert($container instanceof ServiceLocatorInterface);

        $plugin = $container->get(ViewHelperPluginManager::class);
        assert(
            $plugin instanceof ViewHelperPluginManager,
            sprintf(
                '$plugin should be an Instance of %s, but was %s',
                ViewHelperPluginManager::class,
                get_debug_type($plugin),
            ),
        );

        $logger          = $container->get(LoggerInterface::class);
        $htmlify         = $container->get(HtmlifyInterface::class);
        $containerParser = $container->get(ContainerParserInterface::class);
        $escapeHtmlAttr  = $plugin->get(EscapeHtmlAttr::class);
        $renderer        = $container->get(PartialRendererInterface::class);

        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        assert($renderer instanceof PartialRendererInterface);

        return new Menu($container, $logger, $htmlify, $containerParser, $escapeHtmlAttr, $renderer);
    }
}
