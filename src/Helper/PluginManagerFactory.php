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
namespace Mezzio\Navigation\LaminasView\Helper;

use Interop\Container\ContainerInterface;

/**
 * Plugin manager implementation for navigation helpers
 *
 * Enforces that helpers retrieved are instances of
 * Navigation\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
final class PluginManagerFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param ContainerInterface $container
     *
     * @return PluginManager
     */
    public function __invoke(ContainerInterface $container): PluginManager
    {
        return new PluginManager(
            $container
        );
    }
}
