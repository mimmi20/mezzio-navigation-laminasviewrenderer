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
namespace Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\Navigation\Navigation;
use ReflectionProperty;

final class HelperFactory implements FactoryInterface
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws ServiceNotCreatedException
     *
     * @return HelperInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): HelperInterface
    {
        try {
            $serviceLocator = $this->getApplicationServicesFromContainer($container);
        } catch (\ReflectionException $e) {
            throw new ServiceNotCreatedException('could not detect ServiceLocator', 0, $e);
        }

        return new $requestedName(
            $serviceLocator->get(Navigation::class),
            $serviceLocator,
            $serviceLocator->get(Logger::class)
        );
    }

    /**
     * Retrieve the application (parent) services from the container, if possible.
     *
     * @param ContainerInterface $container
     *
     * @throws \ReflectionException
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
