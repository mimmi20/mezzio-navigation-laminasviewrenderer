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
use Laminas\ServiceManager\Factory\FactoryInterface;
use ReflectionProperty;

final class NavigationFactory implements FactoryInterface
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return Navigation
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Navigation
    {
        $serviceLocator = $this->getApplicationServicesFromContainer($container);

        $helper = new Navigation();
        $helper->setServiceLocator($serviceLocator);
        $helper->setPluginManager(new Navigation\PluginManager($serviceLocator));

        return $helper;
    }

    /**
     * Retrieve the application (parent) services from the container, if possible.
     *
     * @param ContainerInterface $container
     *
     * @return ContainerInterface
     */
    private function getApplicationServicesFromContainer(ContainerInterface $container): ContainerInterface
    {
        $r = new ReflectionProperty($container, 'creationContext');
        $r->setAccessible(true);

        return $r->getValue($container) ?: $container;
    }
}
