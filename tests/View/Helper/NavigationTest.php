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
use Laminas\Log\Logger;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\Navigation\Exception\BadMethodCallException;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation;
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

        $view = $this->getMockBuilder(RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(2))
            ->method('setRenderer')
            ->with($view);

        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetInjectTranslator(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

        self::assertTrue($helper->getInjectTranslator());

        $helper->setInjectTranslator(false);

        self::assertFalse($helper->getInjectTranslator());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetInjectAuthorization(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

        self::assertTrue($helper->getInjectAuthorization());

        $helper->setInjectAuthorization(false);

        self::assertFalse($helper->getInjectAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetDefaultProxy(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

        self::assertSame('menu', $helper->getDefaultProxy());

        $helper->setDefaultProxy('links');

        self::assertSame('links', $helper->getDefaultProxy());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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
}
