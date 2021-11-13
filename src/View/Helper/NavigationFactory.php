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

namespace Mezzio\Navigation\LaminasView\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Container\ContainerExceptionInterface;

use function assert;
use function get_class;
use function gettype;
use function is_object;
use function sprintf;

final class NavigationFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Navigation
    {
        assert($container instanceof ServiceLocatorInterface);

        $logger          = $container->get(Logger::class);
        $htmlify         = $container->get(HtmlifyInterface::class);
        $containerParser = $container->get(ContainerParserInterface::class);

        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);

        $helper = new Navigation($container, $logger, $htmlify, $containerParser);

        $plugin = $container->get(Navigation\PluginManager::class);

        assert(
            $plugin instanceof Navigation\PluginManager || $plugin instanceof ViewHelperPluginManager,
            sprintf(
                '$plugin should be an Instance of %s, but was %s',
                Navigation\PluginManager::class,
                is_object($plugin) ? get_class($plugin) : gettype($plugin)
            )
        );

        $helper->setPluginManager($plugin);

        return $helper;
    }
}
