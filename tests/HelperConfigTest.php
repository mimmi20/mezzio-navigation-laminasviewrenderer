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

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Mezzio\Navigation\LaminasView\HelperConfig;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mezzio\Navigation\Service\DefaultNavigationFactory;
use PHPUnit\Framework\TestCase;

/**
 * Tests the class Laminas_Navigation_Page_Mvc
 *
 * @group      Laminas_Navigation
 */
final class HelperConfigTest extends TestCase
{
    /**
     * @return \string[][]
     */
    public function navigationServiceNameProvider(): array
    {
        return [
            ['navigation'],
            ['Navigation'],
            [Navigation::class],
            ['laminasviewhelpernavigation'],
        ];
    }

    /**
     * @dataProvider navigationServiceNameProvider
     *
     * @param string $navigationHelperServiceName
     *
     * @return void
     */
    public function testConfigureServiceManagerWithConfig(string $navigationHelperServiceName): void
    {
        $replacedMenuClass = Navigation\Links::class;

        $serviceManager = new ServiceManager();
        (new Config([
            'services' => [
                'config' => [
                    'navigation_helpers' => [
                        'invokables' => ['menu' => $replacedMenuClass],
                    ],
                    'navigation' => [
                        'file' => __DIR__ . '/_files/navigation.xml',
                        'default' => [
                            [
                                'label' => 'Page 1',
                                'uri' => 'page1.html',
                            ],
                            [
                                'label' => 'MVC Page',
                                'route' => 'foo',
                                'pages' => [
                                    [
                                        'label' => 'Sub MVC Page',
                                        'route' => 'foo',
                                    ],
                                ],
                            ],
                            [
                                'label' => 'Page 3',
                                'uri' => 'page3.html',
                            ],
                        ],
                    ],
                ],
            ],
            'factories' => [
                'Navigation' => DefaultNavigationFactory::class,
                'ViewHelperManager' => static function ($services) {
                    return new HelperPluginManager($services);
                },
            ],
        ]))->configureServiceManager($serviceManager);

        $helpers = $serviceManager->get('ViewHelperManager');
        (new HelperConfig())->configureServiceManager($helpers);

        $menu = $helpers->get($navigationHelperServiceName)->findHelper('menu');
        self::assertInstanceOf($replacedMenuClass, $menu);
    }
}
