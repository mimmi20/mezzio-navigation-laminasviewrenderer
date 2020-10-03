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
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\View\HelperPluginManager;

/**
 * Inject the laminas-view HelperManager with laminas-navigation view helper configuration.
 *
 * This approach is used for backwards compatibility. The HelperConfig class performs
 * work to ensure that the navigation helper and all its sub-helpers are injected
 * with the view helper manager and application container.
 */
final class ViewHelperManagerDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $name
     * @param callable                              $callback
     * @param array|null                            $options
     *
     * @return \Laminas\View\HelperPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null): HelperPluginManager
    {
        $viewHelpers = $callback();
        (new HelperConfig())->configureServiceManager($viewHelpers);

        return $viewHelpers;
    }
}
