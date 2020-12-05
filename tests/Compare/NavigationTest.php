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
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Renderer\PhpRenderer;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mezzio\Navigation\Navigation as Container;
use Mezzio\Navigation\Page\Uri;

/**
 * Tests Mezzio\Navigation\LaminasView\View\Helper\Navigation
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
final class NavigationTest extends AbstractTest
{
    /**
     * Class name for view helper to test
     *
     * @var string
     */
    protected $helperName = Navigation::class;

    /**
     * View helper
     *
     * @var \Mezzio\Navigation\LaminasView\View\Helper\Navigation
     */
    protected $helper;

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Laminas\Config\Exception\InvalidArgumentException
     * @throws \Laminas\Config\Exception\RuntimeException
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $helperPluginManager = $this->serviceManager->get(HelperPluginManager::class);

        // create helper
        $this->helper = new Navigation(
            $this->serviceManager,
            $logger,
            $helperPluginManager->get(HtmlifyInterface::class),
            $helperPluginManager->get(ContainerParserInterface::class)
        );
        $this->helper->setPluginManager(new Navigation\PluginManager($this->serviceManager));

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = $this->helper->__invoke();
        self::assertEquals($this->helper, $returned);
        self::assertEquals($this->nav1, $returned->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = $this->helper->__invoke($this->nav2);
        self::assertEquals($this->helper, $returned);
        self::assertEquals($this->nav2, $returned->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testAcceptAclShouldReturnGracefullyWithUnknownResource(): void
    {
        // setup
        $acl = $this->getAcl();
        $this->helper->setAuthorization($acl['acl']);
        $this->helper->setRole($acl['role']);

        $accepted = $this->helper->accept(
            new Uri(
                [
                    'resource' => 'unknownresource',
                    'privilege' => 'someprivilege',
                ]
            )
        );

        self::assertFalse($accepted);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Laminas\View\Exception\ExceptionInterface
     *
     * @return void
     */
    public function testShouldProxyToMenuHelperByDefault(): void
    {
        $this->helper->setContainer($this->nav1);

        // result
        $expected = $this->getExpected('menu/default1.html');
        $actual   = $this->helper->render();

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testHasContainer(): void
    {
        $oldContainer = $this->helper->getContainer();
        $this->helper->setContainer(null);
        self::assertFalse($this->helper->hasContainer());
        $this->helper->setContainer($oldContainer);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Laminas\View\Exception\ExceptionInterface
     *
     * @return void
     */
    public function testInjectingContainer(): void
    {
        // setup
        $this->helper->setContainer($this->nav2);
        $expected = [
            'menu' => $this->getExpected('menu/default2.html'),
            'breadcrumbs' => $this->getExpected('bc/default.html'),
        ];
        $actual = [];

        // result
        $actual['menu'] = $this->helper->render();
        $this->helper->setContainer($this->nav1);
        $actual['breadcrumbs'] = $this->helper->breadcrumbs()->render();

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Laminas\View\Exception\ExceptionInterface
     *
     * @return void
     */
    public function testDisablingContainerInjection(): void
    {
        // setup
        $this->helper->setInjectContainer(false);
        $this->helper->menu()->setContainer(null);
        $this->helper->breadcrumbs()->setContainer(null);
        $this->helper->setContainer($this->nav2);

        // result
        $expected = [
            'menu' => '',
            'breadcrumbs' => '',
        ];
        $actual = [
            'menu' => $this->helper->render(),
            'breadcrumbs' => $this->helper->breadcrumbs()->render(),
        ];

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testMultipleNavigationsAndOneMenuDisplayedTwoTimes(): void
    {
        $expected = $this->helper->setContainer($this->nav1)->menu()->getContainer();
        $this->helper->setContainer($this->nav2)->menu()->getContainer();
        $actual = $this->helper->setContainer($this->nav1)->menu()->getContainer();

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testServiceManagerIsUsedToRetrieveContainer(): void
    {
        $container = new Container();
        $this->serviceManager->setService('navigation', $container);

        $this->helper->setContainer('navigation');

        $expected = $this->helper->getContainer();
        $actual   = $container;
        self::assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testInjectingAcl(): void
    {
        self::markTestSkipped();
//        // setup
//        $acl = $this->getAcl();
//        $this->helper->setAuthorization($acl['acl']);
//        $this->helper->setRole($acl['role']);
//
//        $expected = $this->_getExpected('menu/acl.html');
//        $actual = $this->helper->render();
//
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testDisablingAclInjection(): void
    {
        self::markTestSkipped();
//        // setup
//        $acl = $this->getAcl();
//        $this->helper->setAuthorization($acl['acl']);
//        $this->helper->setRole($acl['role']);
//        //$this->helper->setInjectAcl(false);
//
//        $expected = $this->_getExpected('menu/default1.html');
//        $actual = $this->helper->render();
//
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     *
     * @requires extension intl
     */
    public function testInjectingTranslator(): void
    {
        self::markTestSkipped();
//        $expected = $this->_getExpected('menu/translated.html');
//        $actual = $this->helper->render();
//
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     *
     * @requires extension intl
     */
    public function testDisablingTranslatorInjection(): void
    {
        self::markTestSkipped();
//        $expected = $this->_getExpected('menu/default1.html');
//        $actual = $this->helper->render();
//
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testTranslatorMethods(): void
    {
        self::markTestSkipped();
//        $translatorMock = $this->prophesize(Translator::class)->reveal();
//        $this->helper->setTranslator($translatorMock, 'foo');
//
//        $this->assertEquals($translatorMock, $this->helper->getTranslator());
//        $this->assertEquals('foo', $this->helper->getTranslatorTextDomain());
//        $this->assertTrue($this->helper->hasTranslator());
//        $this->assertTrue($this->helper->isTranslatorEnabled());
//
//        $this->helper->setTranslatorEnabled(false);
//        $this->assertFalse($this->helper->isTranslatorEnabled());
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testSpecifyingDefaultProxy(): void
    {
        self::markTestSkipped();
//        $expected = [
//            'breadcrumbs' => $this->_getExpected('bc/default.html'),
//            'menu' => $this->_getExpected('menu/default1.html')
//        ];
//        $actual = [];
//
//        // result
//        $this->helper->setDefaultProxy('breadcrumbs');
//        $actual['breadcrumbs'] = $this->helper->render($this->nav1);
//        $this->helper->setDefaultProxy('menu');
//        $actual['menu'] = $this->helper->render($this->nav1);
//
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testgetAuthorizationReturnsNullIfNoAuthorizationInstance(): void
    {
        self::assertNull($this->helper->getAuthorization());
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testgetAuthorizationReturnsAuthorizationInstanceSetWithsetAuthorization(): void
    {
        self::markTestSkipped();
//        $acl = new Acl\Acl();
//        $this->helper->setAuthorization($acl);
//        $this->assertEquals($acl, $this->helper->getAuthorization());
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testgetAuthorizationReturnsAuthorizationInstanceSetWithsetDefaultAuthorization(): void
    {
        self::markTestSkipped();
//        $acl = new Acl\Acl();
//        Navigation::setDefaultAuthorization($acl);
//        $actual = $this->helper->getAuthorization();
//        Navigation::setDefaultAuthorization(null);
//        $this->assertEquals($acl, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testsetDefaultAuthorizationAcceptsNull(): void
    {
        self::markTestSkipped();
//        $acl = new Acl\Acl();
//        Navigation::setDefaultAuthorization($acl);
//        Navigation::setDefaultAuthorization(null);
//        $this->assertNull($this->helper->getAuthorization());
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testsetDefaultAuthorizationAcceptsNoParam(): void
    {
        self::markTestSkipped();
//        $acl = new Acl\Acl();
//        Navigation::setDefaultAuthorization($acl);
//        Navigation::setDefaultAuthorization();
//        $this->assertNull($this->helper->getAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRoleAcceptsString(): void
    {
        $this->helper->setRole('member');
        self::assertEquals('member', $this->helper->getRole());
    }

//    /** @var string */
//    private $errorMessage;
//
//    /**
//     * @param int    $code
//     * @param string $msg
//     * @param string $file
//     * @param int    $line
//     * @param array  $c
//     *
//     * @return void
//     */
//    public function toStringErrorHandler($code, $msg, $file, $line, array $c): void
//    {
//        $this->errorMessage = $msg;
//    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testMagicToStringShouldNotThrowException(): void
    {
        self::markTestSkipped();
//        set_error_handler([$this, 'toStringErrorHandler']);
//        $this->helper->menu()->setPartial([1337]);
//        $this->helper->__toString();
//        restore_error_handler();
//
//        $this->assertContains('array must contain', $this->_errorMessage);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testPageIdShouldBeNormalized(): void
    {
        self::markTestSkipped();
//        $nl = PHP_EOL;
//
//        $pageFactory = new PageFactory();
//
//        $pages = [
//            $pageFactory->factory(
//                [
//                    'label' => 'Page 1',
//                    'id'    => 'p1',
//                    'uri'   => 'p1'
//                ]
//            ),
//            $pageFactory->factory(
//                [
//                    'label' => 'Page 2',
//                    'id'    => 'p2',
//                    'uri'   => 'p2'
//                ]
//            ),
//        ];
//
//        $container = new Container();
//        $container->setPages($pages);
//
//        $expected = '<ul class="navigation">' . $nl
//                  . '    <li>' . $nl
//                  . '        <a id="menu-p1" href="p1">Page 1</a>' . $nl
//                  . '    </li>' . $nl
//                  . '    <li>' . $nl
//                  . '        <a id="menu-p2" href="p2">Page 2</a>' . $nl
//                  . '    </li>' . $nl
//                  . '</ul>';
//
//        $actual = $this->helper->render($container);
//
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     *
     * @group Laminas-6854
     */
    public function testRenderInvisibleItem(): void
    {
        self::markTestSkipped();
//        $pageFactory = new PageFactory();
//
//        $pages = [
//            $pageFactory->factory(
//                [
//                    'label' => 'Page 1',
//                    'id'    => 'p1',
//                    'uri'   => 'p1'
//                ]
//            ),
//            $pageFactory->factory(
//                [
//                    'label'   => 'Page 2',
//                    'id'      => 'p2',
//                    'uri'     => 'p2',
//                    'visible' => false
//                ]
//            ),
//        ];
//
//        $container = new Container();
//        $container->setPages($pages);
//
//        $render = $this->helper->menu()->render($container);
//
//        $this->assertNotContains('p2', $render);
//
//        $this->helper->menu()->setRenderInvisible();
//
//        $render = $this->helper->menu()->render($container);
//
//        $this->assertContains('p2', $render);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testMultipleNavigations(): void
    {
        self::markTestSkipped();
//        $menu     = ($this->helper)('nav1')->menu();
//        $actual   = spl_object_hash($this->nav1);
//        $expected = spl_object_hash($menu->getContainer());
//        $this->assertEquals($expected, $actual);
//
//        $menu     = ($this->helper)('nav2')->menu();
//        $actual   = spl_object_hash($this->nav2);
//        $expected = spl_object_hash($menu->getContainer());
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     *
     * @group #3859
     */
    public function testMultipleNavigationsWithDifferentHelpersAndDifferentContainers(): void
    {
        self::markTestSkipped();
//        $menu     = ($this->helper)('nav1')->menu();
//        $actual   = spl_object_hash($this->nav1);
//        $expected = spl_object_hash($menu->getContainer());
//        $this->assertEquals($expected, $actual);
//
//        $breadcrumbs = ($this->helper)('nav2')->breadcrumbs();
//        $actual      = spl_object_hash($this->nav2);
//        $expected    = spl_object_hash($breadcrumbs->getContainer());
//        $this->assertEquals($expected, $actual);
//
//        $links    = ($this->helper)()->links();
//        $expected = spl_object_hash($links->getContainer());
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     *
     * @group #3859
     */
    public function testMultipleNavigationsWithDifferentHelpersAndSameContainer(): void
    {
        self::markTestSkipped();
//        // Tests
//        $menu     = ($this->helper)('nav1')->menu();
//        $actual   = spl_object_hash($this->nav1);
//        $expected = spl_object_hash($menu->getContainer());
//        $this->assertEquals($expected, $actual);
//
//        $breadcrumbs = ($this->helper)('nav1')->breadcrumbs();
//        $expected    = spl_object_hash($breadcrumbs->getContainer());
//        $this->assertEquals($expected, $actual);
//
//        $links    = ($this->helper)()->links();
//        $expected = spl_object_hash($links->getContainer());
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     *
     * @group #3859
     */
    public function testMultipleNavigationsWithSameHelperAndSameContainer(): void
    {
        self::markTestSkipped();
//        // Test
//        $menu     = ($this->helper)('nav1')->menu();
//        $actual   = spl_object_hash($this->nav1);
//        $expected = spl_object_hash($menu->getContainer());
//        $this->assertEquals($expected, $actual);
//
//        $menu     = ($this->helper)('nav1')->menu();
//        $expected = spl_object_hash($menu->getContainer());
//        $this->assertEquals($expected, $actual);
//
//        $menu    = ($this->helper)()->menu();
//        $expected = spl_object_hash($menu->getContainer());
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetPluginManagerAndView(): void
    {
        $pluginManager = new Navigation\PluginManager(new ServiceManager());
        $view          = new PhpRenderer();

        $this->helper->setPluginManager($pluginManager);
        $this->helper->setView($view);

        self::assertEquals($view, $pluginManager->getRenderer());
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     *
     * @group 49
     */
    public function testInjectsLazyInstantiatedPluginManagerWithCurrentServiceLocator(): void
    {
        self::markTestSkipped();
//        $services = $this->prophesize(ContainerInterface::class)->reveal();
//
//        $plugins = $this->helper->getPluginManager();
//        $this->assertInstanceOf(Navigation\PluginManager::class, $plugins);
//
//        if (method_exists($plugins, 'configure')) {
//            // v3
//            $this->assertAttributeSame($services, 'creationContext', $plugins);
//        } else {
//            // v2
//            $this->assertSame($services, $plugins->getServiceLocator());
//        }
    }

    /**
     * Returns the contens of the expected $file, normalizes newlines
     *
     * @param string $file
     *
     * @return string
     */
    protected function getExpected(string $file): string
    {
        return str_replace("\n", PHP_EOL, parent::getExpected($file));
    }
}
