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
namespace MezzioTest\Navigation\LaminasView\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\Exception\BadMethodCallException;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Uri;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class NavigationTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetPluginManager(): void
    {
        $logger = $this->createMock(Logger::class);
        $view   = $this->createMock(RendererInterface::class);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(2))
            ->method('setRenderer')
            ->with($view);

        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);

        /* @var RendererInterface $view */
        $helper->setView($view);

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetInjectTranslator(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertTrue($helper->getInjectTranslator());

        $helper->setInjectTranslator(false);

        self::assertFalse($helper->getInjectTranslator());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetInjectAuthorization(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertTrue($helper->getInjectAuthorization());

        $helper->setInjectAuthorization(false);

        self::assertFalse($helper->getInjectAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetDefaultProxy(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertSame('menu', $helper->getDefaultProxy());

        $helper->setDefaultProxy('links');

        self::assertSame('links', $helper->getDefaultProxy());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindHelperWithoutPluginManager(): void
    {
        $proxy          = 'menu';
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertNull($helper->findHelper($proxy, false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to find plugin for %s, no PluginManager set', $proxy));

        $helper->findHelper($proxy, true);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindHelperNotInPluginManager(): void
    {
        $proxy          = 'menu';
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(2))
            ->method('has')
            ->with($proxy)
            ->willReturn(false);

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);

        self::assertNull($helper->findHelper($proxy, false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to find plugin for %s', $proxy));

        $helper->findHelper($proxy, true);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindHelperExceptionInPluginManager(): void
    {
        $proxy          = 'menu';
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(2))
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::exactly(2))
            ->method('get')
            ->with($proxy)
            ->willThrowException(new ServiceNotFoundException('test'));

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);

        self::assertNull($helper->findHelper($proxy, false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to load plugin for %s', $proxy));

        $helper->findHelper($proxy, true);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindHelper(): void
    {
        $proxy          = 'menu';
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $menu = $this->getMockBuilder(Navigation\HelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $menu->expects(self::once())
            ->method('setContainer')
            ->with(new IsInstanceOf(\Mezzio\Navigation\Navigation::class));
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with(false);
        $menu->expects(self::once())
            ->method('hasRole')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('hasTranslator')
            ->willReturn(false);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(2))
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::exactly(2))
            ->method('get')
            ->with($proxy)
            ->willReturn($menu);

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);

        self::assertSame($menu, $helper->findHelper($proxy, false));
        self::assertSame($menu, $helper->findHelper($proxy, true));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindHelperWithRule(): void
    {
        $role           = 'test';
        $proxy          = 'menu';
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $menu = $this->getMockBuilder(Navigation\HelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $menu->expects(self::once())
            ->method('setContainer')
            ->with(new IsInstanceOf(\Mezzio\Navigation\Navigation::class));
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with(false);
        $menu->expects(self::once())
            ->method('hasRole')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setRole')
            ->with($role);
        $menu->expects(self::never())
            ->method('hasTranslator');

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(2))
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::exactly(2))
            ->method('get')
            ->with($proxy)
            ->willReturn($menu);

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);
        $helper->setRole($role);
        $helper->setInjectTranslator(false);

        self::assertSame($menu, $helper->findHelper($proxy, false));
        self::assertSame($menu, $helper->findHelper($proxy, true));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderExceptionInPluginManager(): void
    {
        $proxy          = 'menu';
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::once())
            ->method('err')
            ->with(new IsInstanceOf(RuntimeException::class));

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::once())
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::once())
            ->method('get')
            ->with($proxy)
            ->willThrowException(new ServiceNotFoundException('test'));

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);

        self::assertSame('', $helper->render());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRender(): void
    {
        $proxy          = 'menu';
        $container      = null;
        $rendered       = '';
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $menu = $this->getMockBuilder(Navigation\HelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $menu->expects(self::once())
            ->method('setContainer')
            ->with(new IsInstanceOf(\Mezzio\Navigation\Navigation::class));
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with(false);
        $menu->expects(self::once())
            ->method('hasRole')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('hasTranslator')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('render')
            ->with($container)
            ->willReturn($rendered);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::once())
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::once())
            ->method('get')
            ->with($proxy)
            ->willReturn($menu);

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);

        self::assertSame($rendered, $helper->render($container));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     *
     * @return void
     */
    public function testCallExceptionInPluginManager(): void
    {
        $proxy          = 'menu';
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('err');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::once())
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::once())
            ->method('get')
            ->with($proxy)
            ->willThrowException(new ServiceNotFoundException('test'));

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Bad method call: Unknown method Mezzio\Navigation\Navigation::menu');

        $helper->{$proxy}();
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testCall(): void
    {
        $proxy          = 'menu';
        $container      = null;
        $rendered       = '';
        $arguments      = [];
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $menu = $this->getMockBuilder(Navigation\MenuInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $menu->expects(self::once())
            ->method('setContainer')
            ->with(new IsInstanceOf(\Mezzio\Navigation\Navigation::class));
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with(false);
        $menu->expects(self::once())
            ->method('hasRole')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('hasTranslator')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('__invoke')
            ->with($arguments)
            ->willReturn($rendered);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::once())
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::once())
            ->method('get')
            ->with($proxy)
            ->willReturn($menu);

        /* @var Navigation\PluginManager $pluginManager */
        $helper->setPluginManager($pluginManager);

        self::assertSame($rendered, $helper->{$proxy}($arguments));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMinDepth(): void
    {
        $minDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth($minDepth);

        self::assertSame($minDepth, $helper->getMinDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRenderInvisible(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRole(): void
    {
        $role           = 'testRole';
        $defaultRole    = 'testDefaultRole';
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Navigation::setDefaultRole($defaultRole);

        self::assertSame($defaultRole, $helper->getRole());
        self::assertTrue($helper->hasRole());

        $helper->setRole($role);

        self::assertSame($role, $helper->getRole());
        self::assertTrue($helper->hasRole());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetUseAuthorization(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetAuthorization(): void
    {
        $auth           = $this->createMock(AuthorizationInterface::class);
        $defaultAuth    = $this->createMock(AuthorizationInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        /* @var AuthorizationInterface $defaultAuth */
        Navigation::setDefaultAuthorization($defaultAuth);

        self::assertSame($defaultAuth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($auth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetTranslator(): void
    {
        $translator     = $this->createMock(TranslatorInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);
        $textDomain     = 'test';

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertTrue($helper->isTranslatorEnabled());
        self::assertNull($helper->getTranslator());
        self::assertFalse($helper->hasTranslator());
        self::assertSame('default', $helper->getTranslatorTextDomain());

        /* @var TranslatorInterface $translator */
        $helper->setTranslator($translator);
        $helper->setTranslatorTextDomain($textDomain);

        self::assertSame($translator, $helper->getTranslator());
        self::assertSame($textDomain, $helper->getTranslatorTextDomain());
        self::assertTrue($helper->hasTranslator());

        $helper->setTranslatorEnabled(false);

        self::assertNull($helper->getTranslator());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetView(): void
    {
        $view           = $this->createMock(RendererInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertNull($helper->getView());

        /* @var RendererInterface $view */
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainer(): void
    {
        $container      = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(\Mezzio\Navigation\Navigation::class, $container1);
        self::assertTrue($helper->hasContainer());

        /* @var AuthorizationInterface $auth */
        $helper->setContainer();

        $container2 = $helper->getContainer();

        self::assertInstanceOf(\Mezzio\Navigation\Navigation::class, $container2);
        self::assertNotSame($container1, $container2);
        self::assertTrue($helper->hasContainer());

        $helper->setContainer($container);

        self::assertSame($container, $helper->getContainer());
        self::assertTrue($helper->hasContainer());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithNumber(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Container must be a string alias or an instance of Mezzio\Navigation\ContainerInterface');

        $helper->setContainer(1);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithStringDefaultNotFound(): void
    {
        $logger = $this->createMock(Logger::class);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(\Mezzio\Navigation\Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(\Mezzio\Navigation\Navigation::class)
            ->willThrowException(new ServiceNotFoundException('test'));

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', \Mezzio\Navigation\Navigation::class));

        $helper->setContainer('default');
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithStringDefaultFound(): void
    {
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(\Mezzio\Navigation\Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(\Mezzio\Navigation\Navigation::class)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $helper->setContainer('default');

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithStringNavigationNotFound(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([\Mezzio\Navigation\Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', $name));

        $helper->setContainer($name);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithStringNavigationFound(): void
    {
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([\Mezzio\Navigation\Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'default';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([\Mezzio\Navigation\Navigation::class], ['navigation'])
            ->willReturnOnConsecutiveCalls(false, false);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', $name));

        $helper->setContainer($name);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithStringFound(): void
    {
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testDoNotAcceptInvisiblePages(): void
    {
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $helper->setContainer($name);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');

        /* @var PageInterface $page */
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testDoNotAcceptByAuthorization(): void
    {
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $helper->setContainer($name);

        $role = 'testRole';

        $helper->setRole($role);

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(false);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::never())
            ->method('getParent');

        /* @var PageInterface $page */
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testDoNotAcceptByAuthorizationWithParent(): void
    {
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $helper->setContainer($name);

        $role = 'testRole';

        $helper->setRole($role);

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);

        /* @var PageInterface $page */
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlify(): void
    {
        $expected  = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Navigation::class, $page)
            ->willReturn($expected);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $helper->setContainer($name);

        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects(self::never())
            ->method('translate');

        /* @var TranslatorInterface $translator */
        $helper->setTranslator($translator);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        /* @var PhpRenderer $view */
        $helper->setView($view);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->htmlify($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetIndent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveNoActivePages(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $parentPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $parentPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $parentPage->expects(self::once())
            ->method('getParent')
            ->willReturn(null);
        $parentPage->expects(self::never())
            ->method('isActive')
            ->with(false)
            ->willReturn(false);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(false);

        $container = new \Mezzio\Navigation\Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, 0, 42));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveOneActivePage(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $parentPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $parentPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $parentPage->expects(self::once())
            ->method('getParent')
            ->willReturn(null);
        $parentPage->expects(self::never())
            ->method('isActive');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        $container = new \Mezzio\Navigation\Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, 0, 42));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveWithoutContainer(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, 0, 42));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveOneActivePageWithoutDepth(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $parentPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $parentPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $parentPage->expects(self::once())
            ->method('getParent')
            ->willReturn(null);
        $parentPage->expects(self::never())
            ->method('isActive');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        $container = new \Mezzio\Navigation\Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveOneActivePageOutOfRange(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');

        $container = new \Mezzio\Navigation\Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, 2, 42));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveOneActivePageRecursive(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::exactly(2))
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        $parentPage->addPage($page);

        $container = new \Mezzio\Navigation\Navigation();
        $container->addPage($parentPage);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        $helper = new Navigation($serviceLocator, $logger, $htmlify);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::exactly(3))
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $parentPage,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, 0, 0));
    }
}
