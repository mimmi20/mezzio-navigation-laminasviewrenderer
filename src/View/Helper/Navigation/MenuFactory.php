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
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\LaminasViewHelper\Helper\PartialRendererInterface;
use Mezzio\LaminasViewHelper\Helper\PluginManager as LvhPluginManager;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Helper\PluginManager as HelperPluginManager;
use Psr\Container\ContainerExceptionInterface;

use function assert;
use function get_class;
use function gettype;
use function is_object;
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

        $lvhPluginManager = $container->get(LvhPluginManager::class);
        assert(
            $lvhPluginManager instanceof PluginManagerInterface,
            sprintf(
                '$lvhPluginManager should be an Instance of %s, but was %s',
                LvhPluginManager::class,
                is_object($lvhPluginManager) ? get_class($lvhPluginManager) : gettype($lvhPluginManager)
            )
        );

        return new Menu(
            $container,
            $container->get(Logger::class),
            $helperPluginManager->get(HtmlifyInterface::class),
            $helperPluginManager->get(ContainerParserInterface::class),
            $plugin->get(EscapeHtmlAttr::class),
            $lvhPluginManager->get(PartialRendererInterface::class)
        );
    }
}
