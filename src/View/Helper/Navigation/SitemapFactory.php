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

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Helper\PluginManager as HelperPluginManager;
use Psr\Container\ContainerExceptionInterface;

use function assert;
use function get_class;
use function gettype;
use function is_object;
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
        $helperPluginManager = $container->get(HelperPluginManager::class);
        assert(
            $helperPluginManager instanceof PluginManagerInterface,
            sprintf(
                '$helperPluginManager should be an Instance of %s, but was %s',
                HelperPluginManager::class,
                is_object($helperPluginManager) ? get_class($helperPluginManager) : gettype($helperPluginManager)
            )
        );

        $plugin = $container->get(ViewHelperPluginManager::class);
        assert(
            $plugin instanceof ViewHelperPluginManager,
            sprintf(
                '$plugin should be an Instance of %s, but was %s',
                ViewHelperPluginManager::class,
                is_object($plugin) ? get_class($plugin) : gettype($plugin)
            )
        );

        $serverUrlHelper = $plugin->get(ServerUrlHelper::class);
        assert(
            $serverUrlHelper instanceof ServerUrlHelper,
            sprintf(
                '$serverUrlHelper should be an Instance of %s, but was %s',
                ServerUrlHelper::class,
                is_object($serverUrlHelper) ? get_class($serverUrlHelper) : gettype($serverUrlHelper)
            )
        );

        $basePathHelper = $plugin->get(BasePath::class);
        assert(
            $basePathHelper instanceof BasePath,
            sprintf(
                '$basePathHelper should be an Instance of %s, but was %s',
                BasePath::class,
                is_object($basePathHelper) ? get_class($basePathHelper) : gettype($basePathHelper)
            )
        );

        return new Sitemap(
            $container,
            $container->get(Logger::class),
            $helperPluginManager->get(HtmlifyInterface::class),
            $helperPluginManager->get(ContainerParserInterface::class),
            $basePathHelper,
            $plugin->get(EscapeHtml::class),
            $serverUrlHelper
        );
    }
}
