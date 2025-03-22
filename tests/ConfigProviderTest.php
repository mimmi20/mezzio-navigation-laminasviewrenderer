<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView;

use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\LaminasView\UrlHelper;
use Mimmi20\Mezzio\Navigation\LaminasView\ConfigProvider;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    /** @throws Exception */
    public function testProviderDefinesExpectedFactoryServices(): void
    {
        $viewHelperConfig = (new ConfigProvider())->getViewHelperConfig();
        self::assertIsArray($viewHelperConfig);
        self::assertCount(2, $viewHelperConfig);

        self::assertArrayHasKey('factories', $viewHelperConfig);
        $factories = $viewHelperConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(3, $factories);
        self::assertArrayHasKey(Navigation::class, $factories);
        self::assertArrayHasKey(UrlHelper::class, $factories);
        self::assertArrayHasKey(ServerUrlHelper::class, $factories);

        self::assertArrayHasKey('aliases', $viewHelperConfig);
        $aliases = $viewHelperConfig['aliases'];
        self::assertIsArray($aliases);
        self::assertCount(9, $aliases);
        self::assertArrayHasKey('Navigation', $aliases);
        self::assertArrayHasKey('navigation', $aliases);
    }

    /** @throws Exception */
    public function testProviderDefinesExpectedFactoryServices2(): void
    {
        $dependencyConfig = (new ConfigProvider())->getDependencyConfig();
        self::assertIsArray($dependencyConfig);
        self::assertCount(1, $dependencyConfig);

        self::assertArrayHasKey('factories', $dependencyConfig);
        $factories = $dependencyConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(1, $factories);
        self::assertArrayHasKey(Navigation\PluginManager::class, $factories);
    }

    /** @throws Exception */
    public function testInvocationReturnsArrayWithDependencies(): void
    {
        $config = (new ConfigProvider())();

        self::assertIsArray($config);
        self::assertCount(2, $config);
        self::assertArrayHasKey('view_helpers', $config);
        self::assertArrayHasKey('dependencies', $config);

        $viewHelperConfig = $config['view_helpers'];
        self::assertIsArray($viewHelperConfig);
        self::assertCount(2, $viewHelperConfig);

        self::assertArrayHasKey('factories', $viewHelperConfig);
        $factories = $viewHelperConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(3, $factories);
        self::assertArrayHasKey(Navigation::class, $factories);
        self::assertArrayHasKey(UrlHelper::class, $factories);
        self::assertArrayHasKey(ServerUrlHelper::class, $factories);

        self::assertArrayHasKey('aliases', $viewHelperConfig);
        $aliases = $viewHelperConfig['aliases'];
        self::assertIsArray($aliases);
        self::assertCount(9, $aliases);
        self::assertArrayHasKey('Navigation', $aliases);
        self::assertArrayHasKey('navigation', $aliases);

        $dependencyConfig = $config['dependencies'];
        self::assertIsArray($dependencyConfig);
        self::assertCount(1, $dependencyConfig);

        self::assertArrayHasKey('factories', $dependencyConfig);
        $factories = $dependencyConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(1, $factories);
        self::assertArrayHasKey(Navigation\PluginManager::class, $factories);
    }
}
