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
use Mezzio\GenericAuthorization\AuthorizationInterface;
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
    public function testFindHelperWithRule(): void
    {
        $role           = 'test';
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

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMinDepth(): void
    {
        $minDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth($minDepth);

        self::assertSame($minDepth, $helper->getMinDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRenderInvisible(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetUseAuthorization(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Navigation($serviceLocator, $logger);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(\Mezzio\Navigation\Navigation::class, $container1);

        /* @var AuthorizationInterface $auth */
        $helper->setContainer();

        $container2 = $helper->getContainer();

        self::assertInstanceOf(\Mezzio\Navigation\Navigation::class, $container2);
        self::assertNotSame($container1, $container2);

        $helper->setContainer($container);

        self::assertSame($container, $helper->getContainer());
    }
}
