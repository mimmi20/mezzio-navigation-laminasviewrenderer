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

final class HelperFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws ServiceNotCreatedException
     *
     * @return HelperInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName): HelperInterface
    {
        return new $requestedName(
            $container,
            $container->get(Logger::class)
        );
    }
}
