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
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\LaminasView\ServerUrlHelper;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function assert;
use function get_debug_type;
use function sprintf;

final class SitemapFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ViewHelperInterface
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

        $serverUrlHelper = $plugin->get(ServerUrlHelper::class);
        assert(
            $serverUrlHelper instanceof ServerUrlHelper,
            sprintf(
                '$serverUrlHelper should be an Instance of %s, but was %s',
                ServerUrlHelper::class,
                get_debug_type($serverUrlHelper),
            ),
        );

        $basePathHelper = $plugin->get(BasePath::class);
        assert(
            $basePathHelper instanceof BasePath,
            sprintf(
                '$basePathHelper should be an Instance of %s, but was %s',
                BasePath::class,
                get_debug_type($basePathHelper),
            ),
        );

        $logger          = $container->get(LoggerInterface::class);
        $htmlify         = $container->get(HtmlifyInterface::class);
        $containerParser = $container->get(ContainerParserInterface::class);
        $escapeHtml      = $plugin->get(EscapeHtml::class);

        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($escapeHtml instanceof EscapeHtml);

        return new Sitemap(
            $container,
            $logger,
            $htmlify,
            $containerParser,
            $basePathHelper,
            $escapeHtml,
            $serverUrlHelper,
        );
    }
}
