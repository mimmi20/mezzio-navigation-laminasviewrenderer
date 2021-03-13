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
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;

final class PluginManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $name
     * @param array|null         $options
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     *
     * @return \Mezzio\Navigation\LaminasView\View\Helper\Navigation\PluginManager
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null): PluginManager
    {
        $pluginManager = new PluginManager($container, $options ?? []);

        // If this is in a zend-mvc application, the ServiceListener will inject
        // merged configuration during bootstrap.
        if ($container->has('ServiceListener')) {
            return $pluginManager;
        }

        // If we do not have a config service, nothing more to do
        if (!$container->has('config')) {
            return $pluginManager;
        }

        $config = $container->get('config');

        // If we do not have novum-interface configuration, nothing more to do
        if (!isset($config['navigation_helpers']) || !is_array($config['navigation_helpers'])) {
            return $pluginManager;
        }

        // Wire service configuration for identity-interfaces
        (new Config($config['navigation_helpers']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }
}
