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
namespace Mezzio\Navigation\LaminasView\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;

final class NavigationFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param ContainerInterface $container
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     *
     * @return Navigation
     */
    public function __invoke(ContainerInterface $container): Navigation
    {
        $helper = new Navigation(
            $container,
            $container->get(Logger::class),
            $container->get(HtmlifyInterface::class)
        );
        $helper->setPluginManager(new Navigation\PluginManager($container));

        return $helper;
    }
}
