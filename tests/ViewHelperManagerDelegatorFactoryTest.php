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
namespace MezzioTest\Navigation\LaminasView;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Navigation as NavigationHelper;
use Laminas\View\HelperPluginManager;
use Mezzio\Navigation\LaminasView\ViewHelperManagerDelegatorFactory;
use PHPUnit\Framework\TestCase;

final class ViewHelperManagerDelegatorFactoryTest extends TestCase
{
    /**
     * @throws \Interop\Container\Exception\ContainerException
     *
     * @return void
     */
    public function testFactoryConfiguresViewHelperManagerWithNavigationHelpers(): void
    {
        $services = new ServiceManager();
        $helpers  = new HelperPluginManager($services);
        $callback = static function () use ($helpers) {
            return $helpers;
        };

        $factory = new ViewHelperManagerDelegatorFactory();
        self::assertSame($helpers, $factory($services, 'ViewHelperManager', $callback));

        self::assertTrue($helpers->has('navigation'));
        self::assertTrue($helpers->has(NavigationHelper::class));
    }
}
