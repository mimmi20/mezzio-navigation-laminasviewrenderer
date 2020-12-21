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
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\PluginManagerInterface;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;

final class FindFromPropertyFactory implements FactoryInterface
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     *
     * @return FindFromProperty
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FindFromProperty
    {
        $helperPluginManager = $container->get(HelperPluginManager::class);
        \assert(
            $helperPluginManager instanceof PluginManagerInterface,
            sprintf(
                '$helperPluginManager should be an Instance of %s, but was %s',
                HelperPluginManager::class,
                get_class($helperPluginManager)
            )
        );

        $acceptHelper = $helperPluginManager->build(
            AcceptHelperInterface::class,
            [
                'authorization' => $options['authorization'] ?? null,
                'renderInvisible' => $options['renderInvisible'] ?? false,
                'role' => $options['role'] ?? null,
            ]
        );
        \assert($acceptHelper instanceof AcceptHelperInterface);

        return new FindFromProperty(
            $acceptHelper,
            $helperPluginManager->get(ConvertToPagesInterface::class)
        );
    }
}
