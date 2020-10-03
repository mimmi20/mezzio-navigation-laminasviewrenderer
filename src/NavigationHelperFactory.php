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
namespace Mezzio\Navigation\LaminasView;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\Navigation as NavigationHelper;
use ReflectionProperty;

final class NavigationHelperFactory implements FactoryInterface
{
    /**
     * Create and return a navigation helper instance. (v3)
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return NavigationHelper
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NavigationHelper
    {
        $helper = new NavigationHelper();
        $helper->setServiceLocator($this->getApplicationServicesFromContainer($container));

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
