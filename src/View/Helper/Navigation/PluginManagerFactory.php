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
use Psr\Container\ContainerExceptionInterface;

use function array_key_exists;
use function assert;
use function is_array;

final class PluginManagerFactory implements FactoryInterface
{
    /**
     * @param string            $name
     * @param array<mixed>|null $options
     *
     * @throws ContainerExceptionInterface
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
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

        assert(is_array($config));

        // If we do not have navigation helper configuration, nothing more to do
        if (!array_key_exists('navigation_helpers', $config) || !is_array($config['navigation_helpers'])) {
            return $pluginManager;
        }

        // Wire service configuration for navigation helpers
        (new Config($config['navigation_helpers']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }
}
