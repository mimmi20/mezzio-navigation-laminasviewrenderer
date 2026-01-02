<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Compare;

use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mimmi20\Mezzio\Navigation\Navigation as Container;
use Mimmi20\Mezzio\Navigation\Page\PageFactory;
use Mimmi20\Mezzio\Navigation\Page\Uri;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use Psr\Container\ContainerExceptionInterface;

use function assert;
use function is_string;
use function spl_object_hash;
use function str_replace;

use const PHP_EOL;

/**
 * Tests Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation
 */
#[Group('Compare')]
#[Group('Laminas_View')]
#[Group('Laminas_View_Helper')]
final class NavigationTest extends AbstractTestCase
{
    /**
     * View helper
     *
     * @var Navigation
     */
    private Navigation\ViewHelperInterface $helper;

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceManager->get(ViewHelperPluginManager::class);
        $this->serviceManager->get(LaminasViewRenderer::class);

        $htmlify         = $this->serviceManager->get(HtmlifyInterface::class);
        $containerParser = $this->serviceManager->get(ContainerParserInterface::class);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);

        // create helper
        $this->helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);
        $this->helper->setPluginManager(new Navigation\PluginManager($this->serviceManager));

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);
    }

    /** @throws void */
    #[Override]
    protected function tearDown(): void
    {
        Navigation::setDefaultAuthorization();
        Navigation::setDefaultRole();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = ($this->helper)();
        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav1, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = ($this->helper)($this->nav2);
        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav2, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws ExceptionInterface
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     */
    public function testAcceptAclShouldReturnGracefullyWithUnknownResource(): void
    {
        // setup
        $auth = $this->getAcl();
        assert($auth['acl'] instanceof AuthorizationInterface);
        $this->helper->setAuthorization($auth['acl']);
        assert(is_string($auth['role']));
        $this->helper->setRoles([$auth['role']]);
        $this->helper->setUseAuthorization();

        $accepted = $this->helper->accept(
            new Uri(
                [
                    'resource' => 'unknownresource',
                    'privilege' => 'someprivilege',
                ],
            ),
        );

        self::assertFalse($accepted);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testShouldProxyToMenuHelperByDefault(): void
    {
        $this->helper->setContainer($this->nav1);

        // result
        $expected = $this->getExpected('menu/default1.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testHasContainer(): void
    {
        $oldContainer = $this->helper->getContainer();
        $this->helper->setContainer();
        self::assertFalse($this->helper->hasContainer());
        $this->helper->setContainer($oldContainer);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testInjectingContainer(): void
    {
        // setup
        $this->helper->setContainer($this->nav2);
        $expected = [
            'menu' => $this->getExpected('menu/default2.html'),
            'breadcrumbs' => $this->getExpected('bc/default.html'),
        ];
        $actual   = [];

        // result
        $actual['menu'] = $this->helper->render();
        $this->helper->setContainer($this->nav1);
        $actual['breadcrumbs'] = $this->helper->breadcrumbs()->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testDisablingContainerInjection(): void
    {
        // setup
        $this->helper->setInjectContainer(false);
        $this->helper->menu()->setContainer();
        $this->helper->breadcrumbs()->setContainer();
        $this->helper->setContainer($this->nav2);

        // result
        $expected = [
            'menu' => '',
            'breadcrumbs' => '',
        ];
        $actual   = [
            'menu' => $this->helper->render(),
            'breadcrumbs' => $this->helper->breadcrumbs()->render(),
        ];

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testMultipleNavigationsAndOneMenuDisplayedTwoTimes(): void
    {
        $expected = $this->helper->setContainer($this->nav1)->menu()->getContainer();
        $this->helper->setContainer($this->nav2)->menu()->getContainer();
        $actual = $this->helper->setContainer($this->nav1)->menu()->getContainer();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ContainerModificationsNotAllowedException
     * @throws InvalidArgumentException
     */
    public function testServiceManagerIsUsedToRetrieveContainer(): void
    {
        $container = new Container();
        $this->serviceManager->setService('navigation', $container);

        $this->helper->setContainer('navigation');

        $expected = $this->helper->getContainer();
        $actual   = $container;
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     */
    public function testInjectingAuthorization(): void
    {
        // setup
        $auth = $this->getAcl();
        assert($auth['acl'] instanceof AuthorizationInterface);
        $this->helper->setAuthorization($auth['acl']);
        assert(is_string($auth['role']));
        $this->helper->setRoles([$auth['role']]);
        $this->helper->setUseAuthorization();

        $expected = $this->getExpected('menu/acl.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     */
    public function testDisablingInjectAuthorization(): void
    {
        // setup
        $auth = $this->getAcl();
        assert($auth['acl'] instanceof AuthorizationInterface);
        $this->helper->setAuthorization($auth['acl']);
        assert(is_string($auth['role']));
        $this->helper->setRoles([$auth['role']]);
        $this->helper->setInjectAuthorization(false);

        $expected = $this->getExpected('menu/default1.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testSpecifyingDefaultProxy(): void
    {
        $expected = [
            'breadcrumbs' => $this->getExpected('bc/default.html'),
            'menu' => $this->getExpected('menu/default1.html'),
        ];
        $actual   = [];

        // result
        $this->helper->setDefaultProxy('breadcrumbs');
        $actual['breadcrumbs'] = $this->helper->render($this->nav1);
        $this->helper->setDefaultProxy('menu');
        $actual['menu'] = $this->helper->render($this->nav1);

        self::assertSame($expected, $actual);
    }

    /** @throws Exception */
    public function testgetAuthorizationReturnsNullIfNoAuthorizationInstance(): void
    {
        self::assertNull($this->helper->getAuthorization());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testgetAuthorizationReturnsAuthorizationInstanceSetWithsetAuthorization(): void
    {
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        assert($auth instanceof AuthorizationInterface);
        $this->helper->setAuthorization($auth);
        self::assertSame($auth, $this->helper->getAuthorization());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testgetAuthorizationReturnsAuthorizationInstanceSetWithsetDefaultAuthorization(): void
    {
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        Navigation::setDefaultAuthorization($auth);
        $actual = $this->helper->getAuthorization();
        Navigation::setDefaultAuthorization();
        self::assertSame($auth, $actual);
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testsetDefaultAuthorizationAcceptsNull(): void
    {
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        Navigation::setDefaultAuthorization($auth);
        Navigation::setDefaultAuthorization();
        self::assertNull($this->helper->getAuthorization());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testsetDefaultAuthorizationAcceptsNoParam(): void
    {
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        Navigation::setDefaultAuthorization($auth);
        Navigation::setDefaultAuthorization();
        self::assertNull($this->helper->getAuthorization());
    }

    /** @throws Exception */
    public function testSetRoleAcceptsArrays(): void
    {
        $this->helper->setRoles(['member']);
        self::assertSame(['member'], $this->helper->getRoles());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    public function testPageIdShouldBeNormalized(): void
    {
        $nl = PHP_EOL;

        $pageFactory = new PageFactory();

        $pages = [
            $pageFactory->factory(
                [
                    'label' => 'Page 1',
                    'id' => 'p1',
                    'uri' => 'p1',
                ],
            ),
            $pageFactory->factory(
                [
                    'label' => 'Page 2',
                    'id' => 'p2',
                    'uri' => 'p2',
                ],
            ),
        ];

        $container = new Container();
        $container->setPages($pages);

        $expected = '<ul class="navigation">' . $nl
            . '    <li>' . $nl
            . '        <a id="menu-p1" href="p1">Page 1</a>' . $nl
            . '    </li>' . $nl
            . '    <li>' . $nl
            . '        <a id="menu-p2" href="p2">Page 2</a>' . $nl
            . '    </li>' . $nl
            . '</ul>';

        $actual = $this->helper->render($container);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws ExceptionInterface
     */
    #[Group('Laminas-6854')]
    public function testRenderInvisibleItem(): void
    {
        $pageFactory = new PageFactory();

        $pages = [
            $pageFactory->factory(
                [
                    'label' => 'Page 1',
                    'id' => 'p1',
                    'uri' => 'p1',
                ],
            ),
            $pageFactory->factory(
                [
                    'label' => 'Page 2',
                    'id' => 'p2',
                    'uri' => 'p2',
                    'visible' => false,
                ],
            ),
        ];

        $container = new Container();
        $container->setPages($pages);

        $render = $this->helper->menu()->render($container);

        self::assertStringNotContainsString('p2', $render);

        $this->helper->menu()->setRenderInvisible();

        $render = $this->helper->menu()->render($container);

        self::assertStringContainsString('p2', $render);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testMultipleNavigations(): void
    {
        $menu     = ($this->helper)('nav1')->menu();
        $actual   = spl_object_hash($this->nav1);
        $expected = spl_object_hash($menu->getContainer());
        self::assertSame($this->nav1, $menu->getContainer());
        self::assertSame($expected, $actual);

        $menu     = ($this->helper)('nav2')->menu();
        $actual   = spl_object_hash($this->nav2);
        $expected = spl_object_hash($menu->getContainer());
        self::assertSame($this->nav2, $menu->getContainer());
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    #[Group('#3859')]
    public function testMultipleNavigationsWithDifferentHelpersAndDifferentContainers(): void
    {
        $menu     = ($this->helper)('nav1')->menu();
        $actual   = spl_object_hash($this->nav1);
        $expected = spl_object_hash($menu->getContainer());
        self::assertSame($expected, $actual);

        $breadcrumbs = ($this->helper)('nav2')->breadcrumbs();
        $actual      = spl_object_hash($this->nav2);
        $expected    = spl_object_hash($breadcrumbs->getContainer());
        self::assertSame($expected, $actual);

        $links    = ($this->helper)()->links();
        $expected = spl_object_hash($links->getContainer());
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    #[Group('#3859')]
    public function testMultipleNavigationsWithDifferentHelpersAndSameContainer(): void
    {
        // Tests
        $menu     = ($this->helper)('nav1')->menu();
        $actual   = spl_object_hash($this->nav1);
        $expected = spl_object_hash($menu->getContainer());
        self::assertSame($expected, $actual);

        $breadcrumbs = ($this->helper)('nav1')->breadcrumbs();
        $expected    = spl_object_hash($breadcrumbs->getContainer());
        self::assertSame($expected, $actual);

        $links    = ($this->helper)()->links();
        $expected = spl_object_hash($links->getContainer());
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    #[Group('#3859')]
    public function testMultipleNavigationsWithSameHelperAndSameContainer(): void
    {
        // Test
        $menu     = ($this->helper)('nav1')->menu();
        $actual   = spl_object_hash($this->nav1);
        $expected = spl_object_hash($menu->getContainer());
        self::assertSame($expected, $actual);

        $menu     = ($this->helper)('nav1')->menu();
        $expected = spl_object_hash($menu->getContainer());
        self::assertSame($expected, $actual);

        $menu     = ($this->helper)()->menu();
        $expected = spl_object_hash($menu->getContainer());
        self::assertSame($expected, $actual);
    }

    /** @throws Exception */
    public function testSetPluginManagerAndView(): void
    {
        $pluginManager = new Navigation\PluginManager(new ServiceManager());
        assert($pluginManager instanceof Navigation\PluginManager);
        $view = new PhpRenderer();

        $this->helper->setPluginManager($pluginManager);
        $this->helper->setView($view);

        self::assertSame($view, $pluginManager->getRenderer());
    }

    /**
     * Returns the contens of the expected $file, normalizes newlines
     *
     * @throws Exception
     */
    #[Override]
    protected function getExpected(string $file): string
    {
        return str_replace("\n", PHP_EOL, parent::getExpected($file));
    }
}
