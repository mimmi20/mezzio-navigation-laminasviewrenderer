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
namespace MezzioTest\Navigation\LaminasView\Compare;

use Laminas\Log\Logger;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\Helper\UrlHelper as BaseUrlHelper;
use Mezzio\LaminasView\HelperPluginManagerFactory;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\LaminasView\LaminasViewRendererFactory;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\LaminasView\UrlHelper;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;
use Mezzio\Navigation\LaminasView\Helper\PluginManagerFactory;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\HelperInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\PluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\NavigationFactory;
use Mezzio\Navigation\LaminasView\View\Helper\ServerUrlHelperFactory;
use Mezzio\Navigation\LaminasView\View\Helper\UrlHelperFactory;
use Mezzio\Navigation\Navigation;
use Mezzio\Navigation\Page\PageFactory;
use Mezzio\Navigation\Page\PageFactoryInterface;
use Mezzio\Navigation\Service\ConstructedNavigationFactory;
use Mezzio\Navigation\Service\DefaultNavigationFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 */
final class PluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    /**
     * @return \Mezzio\Navigation\LaminasView\View\Helper\Navigation\PluginManager
     */
    protected function getPluginManager(): PluginManager
    {
        $sm = new ServiceManager();
        $sm->setAllowOverride(true);

        $sm->setFactory('Navigation', DefaultNavigationFactory::class);
        $sm->setFactory('nav_test1', new ConstructedNavigationFactory('nav_test1'));
        $sm->setFactory('nav_test2', new ConstructedNavigationFactory('nav_test2'));
        $sm->setFactory('nav_test3', new ConstructedNavigationFactory('nav_test3'));
        $sm->setFactory(PageFactory::class, InvokableFactory::class);
        $sm->setAlias(PageFactoryInterface::class, PageFactory::class);
        $sm->setFactory(HelperPluginManager::class, PluginManagerFactory::class);
        $sm->setFactory(ViewHelperPluginManager::class, HelperPluginManagerFactory::class);
        $sm->setFactory(LaminasViewRenderer::class, LaminasViewRendererFactory::class);
        $sm->setFactory(BaseServerUrlHelper::class, InvokableFactory::class);
        $sm->setFactory(Logger::class, InvokableFactory::class);
        $sm->setFactory(
            'config',
            static function () {
                return [
                    'view_helpers' => [
                        'aliases' => [
                            'navigation' => \Mezzio\Navigation\LaminasView\View\Helper\Navigation::class,
                            'Navigation' => Navigation::class,
                            BaseServerUrlHelper::class => ServerUrlHelper::class,
                            'serverurl' => ServerUrlHelper::class,
                            'serverUrl' => ServerUrlHelper::class,
                            'ServerUrl' => ServerUrlHelper::class,
                            BaseUrlHelper::class => UrlHelper::class,
                            'url' => UrlHelper::class,
                            'Url' => UrlHelper::class,
                        ],
                        'factories' => [
                            Navigation::class => NavigationFactory::class,
                            UrlHelper::class => UrlHelperFactory::class,
                            ServerUrlHelper::class => ServerUrlHelperFactory::class,
                        ],
                    ],
                ];
            }
        );

        return new PluginManager($sm);
    }

    /**
     * @return string
     */
    protected function getV2InvalidPluginException(): string
    {
        return InvalidHelperException::class;
    }

    /**
     * @return string
     */
    protected function getInstanceOf(): string
    {
        return HelperInterface::class;
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     *
     * @group 43
     */
    public function testConstructorArgumentsAreOptionalUnderV2(): void
    {
        $helpers = $this->getPluginManager();

        if (method_exists($helpers, 'configure')) {
            self::markTestSkipped('laminas-servicemanager v3 plugin managers require a container argument');
        }

        $helpers = new PluginManager();
        self::assertInstanceOf(PluginManager::class, $helpers);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     *
     * @group 43
     */
    public function testConstructorAllowsConfigInstanceAsFirstArgumentUnderV2(): void
    {
        $helpers = $this->getPluginManager();

        if (method_exists($helpers, 'configure')) {
            self::markTestSkipped('laminas-servicemanager v3 plugin managers require a container argument');
        }

        $config = new Config([]);

        self::assertInstanceOf(ConfigInterface::class, $config);

        $helpers = new PluginManager($config);
        self::assertInstanceOf(PluginManager::class, $helpers);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Laminas\ServiceManager\Exception\InvalidServiceException
     *
     * @return void
     */
    public function testInjectsParentContainerIntoHelpers(): void
    {
        $sm = new ServiceManager();
        $sm->setAllowOverride(true);

        $sm->setFactory('Navigation', DefaultNavigationFactory::class);
        $sm->setFactory('nav_test1', new ConstructedNavigationFactory('nav_test1'));
        $sm->setFactory('nav_test2', new ConstructedNavigationFactory('nav_test2'));
        $sm->setFactory('nav_test3', new ConstructedNavigationFactory('nav_test3'));
        $sm->setFactory(PageFactory::class, InvokableFactory::class);
        $sm->setAlias(PageFactoryInterface::class, PageFactory::class);
        $sm->setFactory(HelperPluginManager::class, PluginManagerFactory::class);
        $sm->setFactory(
            'config',
            static function () {
                return [
                    'navigation' => [
                        'default' => [],
                    ],
                    'view_helpers' => [
                        'aliases' => [
                            'navigation' => \Mezzio\Navigation\LaminasView\View\Helper\Navigation::class,
                            'Navigation' => Navigation::class,
                            BaseServerUrlHelper::class => ServerUrlHelper::class,
                            'serverurl' => ServerUrlHelper::class,
                            'serverUrl' => ServerUrlHelper::class,
                            'ServerUrl' => ServerUrlHelper::class,
                            BaseUrlHelper::class => UrlHelper::class,
                            'url' => UrlHelper::class,
                            'Url' => UrlHelper::class,
                        ],
                        'factories' => [
                            Navigation::class => NavigationFactory::class,
                            UrlHelper::class => UrlHelperFactory::class,
                            ServerUrlHelper::class => ServerUrlHelperFactory::class,
                        ],
                    ],
                ];
            }
        );
        $sm->setFactory(ViewHelperPluginManager::class, HelperPluginManagerFactory::class);
        $sm->setFactory(LaminasViewRenderer::class, LaminasViewRendererFactory::class);
        $sm->setFactory(BaseServerUrlHelper::class, InvokableFactory::class);
        $sm->setFactory(Logger::class, InvokableFactory::class);
        $helpers = new PluginManager($sm);

        $helper = $helpers->get('breadcrumbs');
        self::assertInstanceOf(Breadcrumbs::class, $helper);
        self::assertSame($sm, $helper->getServiceLocator());
    }

    /**
     * @return void
     */
    public function testRegisteringInvalidElementRaisesException(): void
    {
        $this->expectException($this->getServiceNotFoundException());
        $this->getPluginManager()->setService('test', $this);
    }

    /**
     * @throws \Laminas\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Laminas\ServiceManager\Exception\InvalidServiceException
     *
     * @return void
     */
    public function testLoadingInvalidElementRaisesException(): void
    {
        $manager = $this->getPluginManager();
        $manager->setInvokableClass('test', self::class);
        $this->expectException($this->getServiceNotFoundException());
        $manager->get('test');
    }
}
