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

namespace MezzioTest\Navigation\LaminasView;

use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\LaminasView\UrlHelper;
use Mezzio\Navigation\LaminasView\ConfigProvider;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ConfigProviderTest extends TestCase
{
    private ConfigProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testProviderDefinesExpectedFactoryServices(): void
    {
        $viewHelperConfig = $this->provider->getViewHelperConfig();
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

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testProviderDefinesExpectedFactoryServices2(): void
    {
        $dependencyConfig = $this->provider->getDependencyConfig();
        self::assertIsArray($dependencyConfig);
        self::assertCount(1, $dependencyConfig);

        self::assertArrayHasKey('factories', $dependencyConfig);
        $factories = $dependencyConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(1, $factories);
        self::assertArrayHasKey(Navigation\PluginManager::class, $factories);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvocationReturnsArrayWithDependencies(): void
    {
        $config = ($this->provider)();

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
