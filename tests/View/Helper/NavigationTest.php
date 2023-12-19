<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\DomainException;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\Exception\BadMethodCallException;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\Mezzio\Navigation\Page\Uri;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function assert;
use function sprintf;

final class NavigationTest extends TestCase
{
    /** @throws void */
    protected function tearDown(): void
    {
        Navigation::setDefaultAuthorization(null);
        Navigation::setDefaultRole(null);
    }

    /** @throws Exception */
    public function testSetPluginManager(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $view = $this->createMock(RendererInterface::class);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::once())
            ->method('setRenderer')
            ->with($view);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
        self::assertSame($serviceLocator, $helper->getServiceLocator());
    }

    /** @throws Exception */
    public function testSetPluginManager2(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $view = $this->createMock(RendererInterface::class);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::once())
            ->method('setRenderer')
            ->with($view);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertSame($view, $helper->getView());
        self::assertSame($serviceLocator, $helper->getServiceLocator());
    }

    /** @throws Exception */
    public function testSetInjectAuthorization(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertTrue($helper->getInjectAuthorization());

        $helper->setInjectAuthorization(false);

        self::assertFalse($helper->getInjectAuthorization());

        $helper->setInjectAuthorization();

        self::assertTrue($helper->getInjectAuthorization());
    }

    /** @throws Exception */
    public function testSetDefaultProxy(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertSame('menu', $helper->getDefaultProxy());

        $helper->setDefaultProxy('links');

        self::assertSame('links', $helper->getDefaultProxy());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindHelperWithoutPluginManager(): void
    {
        $proxy = 'menu';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertNull($helper->findHelper($proxy, false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('Failed to find plugin for %s, no PluginManager set', $proxy),
        );
        $this->expectExceptionCode(0);

        $helper->findHelper($proxy, true);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindHelperNotInPluginManager(): void
    {
        $proxy = 'menu';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(2))
            ->method('has')
            ->with($proxy)
            ->willReturn(false);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertNull($helper->findHelper($proxy, false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to find plugin for %s', $proxy));
        $this->expectExceptionCode(0);

        $helper->findHelper($proxy, true);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindHelperNotInPluginManager2(): void
    {
        $proxy = 'menu';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(2))
            ->method('has')
            ->with($proxy)
            ->willReturn(false);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertNull($helper->findHelper($proxy, false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to find plugin for %s', $proxy));
        $this->expectExceptionCode(0);

        $helper->findHelper($proxy);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindHelperExceptionInPluginManager(): void
    {
        $proxy     = 'menu';
        $exception = new ServiceNotFoundException('test');

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::once())
            ->method('debug')
            ->with($exception);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

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
            ->willThrowException($exception);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertNull($helper->findHelper($proxy, false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to load plugin for %s', $proxy));
        $this->expectExceptionCode(0);

        $helper->findHelper($proxy, true);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindHelperExceptionInPluginManager2(): void
    {
        $proxy     = 'menu';
        $exception = new ServiceNotFoundException('test');

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::once())
            ->method('debug')
            ->with($exception);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

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
            ->willThrowException($exception);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertNull($helper->findHelper($proxy, false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to load plugin for %s', $proxy));
        $this->expectExceptionCode(0);

        $helper->findHelper($proxy);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindHelper(): void
    {
        $proxy = 'menu';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $container1 = $this->getMockBuilder(\Mimmi20\Mezzio\Navigation\ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container2 = $this->getMockBuilder(\Mimmi20\Mezzio\Navigation\ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($container1)
            ->willReturn($container2);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $menu    = $this->getMockBuilder(Navigation\ViewHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher = self::exactly(4);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function ($container = null) use ($matcher, $container2): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertNull($container),
                        default => self::assertSame($container2, $container),
                    };
                },
            );
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with(null);
        $menu->expects(self::once())
            ->method('hasRole')
            ->willReturn(false);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(3))
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::exactly(3))
            ->method('get')
            ->with($proxy)
            ->willReturn($menu);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);
        $helper->setContainer($container1);

        self::assertSame($menu, $helper->findHelper($proxy, false));
        self::assertSame($menu, $helper->findHelper($proxy, true));
        self::assertSame($menu, $helper->findHelper($proxy));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindHelperWithRule(): void
    {
        $role  = 'test';
        $proxy = 'menu';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $menu    = $this->getMockBuilder(Navigation\ViewHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher = self::exactly(4);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function ($container = null) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertNull($container),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                        ),
                    };
                },
            );
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with(null);
        $menu->expects(self::once())
            ->method('hasRole')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setRole')
            ->with($role);

        $pluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects(self::exactly(3))
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::exactly(3))
            ->method('get')
            ->with($proxy)
            ->willReturn($menu);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);
        $helper->setRole($role);

        self::assertSame($menu, $helper->findHelper($proxy, false));
        self::assertSame($menu, $helper->findHelper($proxy, true));
        self::assertSame($menu, $helper->findHelper($proxy));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testRenderExceptionInPluginManager(): void
    {
        $proxy          = 'menu';
        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::once())
            ->method('error')
            ->with(new IsInstanceOf(RuntimeException::class));
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

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

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertSame('', $helper->render());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testRender(): void
    {
        $proxy     = 'menu';
        $container = null;
        $rendered  = '';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $menu    = $this->getMockBuilder(Navigation\ViewHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher = self::exactly(2);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function ($container = null) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertNull($container),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                        ),
                    };
                },
            );
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with(null);
        $menu->expects(self::once())
            ->method('hasRole')
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

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertSame($rendered, $helper->render($container));
    }

    /** @throws Exception */
    public function testCallExceptionInPluginManager(): void
    {
        $proxy          = 'menu';
        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::once())
            ->method('error')
            ->with(new IsInstanceOf(RuntimeException::class));
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

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

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Bad method call: Unknown method Mimmi20\Mezzio\Navigation\Navigation::menu',
        );
        $this->expectExceptionCode(0);

        $helper->{$proxy}();
    }

    /** @throws Exception */
    public function testCall(): void
    {
        $proxy     = 'menu';
        $rendered  = '';
        $arguments = null;

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $menu    = $this->getMockBuilder(Navigation\MenuInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher = self::exactly(2);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function (\Mimmi20\Mezzio\Navigation\ContainerInterface | string | null $container = null) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertNull($container),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                        ),
                    };
                },
            );
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with(null);
        $menu->expects(self::once())
            ->method('hasRole')
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

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertSame($rendered, $helper->{$proxy}($arguments));
    }

    /** @throws Exception */
    public function testSetMaxDepth(): void
    {
        $maxDepth = 4;

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /** @throws Exception */
    public function testSetMinDepth(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth(4);

        self::assertSame(4, $helper->getMinDepth());

        $helper->setMinDepth(-1);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth(0);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth(1);

        self::assertSame(1, $helper->getMinDepth());

        $helper->setMinDepth(4);

        self::assertSame(4, $helper->getMinDepth());
    }

    /** @throws Exception */
    public function testSetRenderInvisible(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /** @throws Exception */
    public function testSetRole(): void
    {
        $role        = 'testRole';
        $defaultRole = 'testDefaultRole';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Navigation::setDefaultRole($defaultRole);

        self::assertSame($defaultRole, $helper->getRole());
        self::assertTrue($helper->hasRole());

        $helper->setRole($role);

        self::assertSame($role, $helper->getRole());
        self::assertTrue($helper->hasRole());
    }

    /** @throws Exception */
    public function testSetUseAuthorization(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());

        $helper->setUseAuthorization();

        self::assertTrue($helper->getUseAuthorization());
    }

    /** @throws Exception */
    public function testSetAuthorization(): void
    {
        $auth        = $this->createMock(AuthorizationInterface::class);
        $defaultAuth = $this->createMock(AuthorizationInterface::class);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        assert($defaultAuth instanceof AuthorizationInterface);
        Navigation::setDefaultAuthorization($defaultAuth);

        self::assertSame($defaultAuth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame($auth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());
    }

    /** @throws Exception */
    public function testSetView(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertNull($helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (\Mimmi20\Mezzio\Navigation\ContainerInterface | null $containerParam) use ($matcher, $container): \Mimmi20\Mezzio\Navigation\ContainerInterface | null {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => null,
                        default => $container,
                    };
                },
            );

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(\Mimmi20\Mezzio\Navigation\Navigation::class, $container1);
        self::assertTrue($helper->hasContainer());

        $helper->setContainer();

        $container2 = $helper->getContainer();

        self::assertInstanceOf(\Mimmi20\Mezzio\Navigation\Navigation::class, $container2);
        self::assertNotSame($container1, $container2);
        self::assertTrue($helper->hasContainer());

        $helper->setContainer($container);

        self::assertSame($container, $helper->getContainer());
        self::assertTrue($helper->hasContainer());
    }

    /** @throws Exception */
    public function testSetInjectContainer(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertTrue($helper->getInjectContainer());

        $helper->setInjectContainer(false);

        self::assertFalse($helper->getInjectContainer());

        $helper->setInjectContainer();

        self::assertTrue($helper->getInjectContainer());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'default';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException(new InvalidArgumentException('test'));

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testSetContainerWithStringFound(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mimmi20\\Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testDoNotAccept(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mimmi20\\Mezzio\\Navigation\\Top';

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(false);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $role = 'testRole';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($acceptHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setContainer($name);
        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testHtmlify(): void
    {
        $expected = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mimmi20\\Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setContainer($name);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );
        self::assertSame($expected, $helper->htmlify($page));
    }

    /** @throws Exception */
    public function testSetIndent(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveNoActivePages(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::never())
            ->method('isVisible');
        $parentPage->expects(self::never())
            ->method('getResource');
        $parentPage->expects(self::never())
            ->method('getPrivilege');
        $parentPage->expects(self::never())
            ->method('getParent');
        $parentPage->expects(self::never())
            ->method('isActive');

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

        $container = new \Mimmi20\Mezzio\Navigation\Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePage(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::never())
            ->method('isVisible');
        $parentPage->expects(self::never())
            ->method('getResource');
        $parentPage->expects(self::never())
            ->method('getPrivilege');
        $parentPage->expects(self::never())
            ->method('getParent');
        $parentPage->expects(self::never())
            ->method('isActive');

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

        $container = new \Mimmi20\Mezzio\Navigation\Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 0,
                ],
            );

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveWithoutContainer(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with(new IsInstanceOf(\Mimmi20\Mezzio\Navigation\Navigation::class), $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with(null)
            ->willReturn(null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageWithoutDepth(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::never())
            ->method('isVisible');
        $parentPage->expects(self::never())
            ->method('getResource');
        $parentPage->expects(self::never())
            ->method('getPrivilege');
        $parentPage->expects(self::never())
            ->method('getParent');
        $parentPage->expects(self::never())
            ->method('isActive');

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

        $container = new \Mimmi20\Mezzio\Navigation\Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 0,
                ],
            );

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        $helper->setMinDepth($minDepth);
        $helper->setMaxDepth($maxDepth);

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageOutOfRange(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

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

        $container = new \Mimmi20\Mezzio\Navigation\Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 2;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageRecursive(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

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

        $parentPage->addPage($page);

        $container = new \Mimmi20\Mezzio\Navigation\Navigation();
        $container->addPage($parentPage);

        $role     = 'testRole';
        $maxDepth = 0;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $parentPage,
                    'depth' => 0,
                ],
            );

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $parentPage,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageRecursive2(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setActive(true);
        $parentPage->setUri('parent');
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page1 = new Uri();
        $page1->setActive(true);
        $page1->setUri('test1');

        $page2 = new Uri();
        $page2->setActive(true);
        $page1->setUri('test2');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setActive(true);
        $parentParentPage->setUri('parentParent');

        $parentParentParentPage = new Uri();
        $parentParentParentPage->setVisible(true);
        $parentParentParentPage->setActive(true);
        $parentParentParentPage->setUri('parentParentParent');

        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $container = new \Mimmi20\Mezzio\Navigation\Navigation();
        $container->addPage($parentParentParentPage);

        $role     = 'testRole';
        $maxDepth = 1;
        $minDepth = 2;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageRecursive3(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $name = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setActive(true);
        $parentPage->setUri('parent');
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page1 = new Uri();
        $page1->setActive(true);
        $page1->setUri('test1');

        $page2 = new Uri();
        $page2->setActive(true);
        $page1->setUri('test2');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setActive(true);
        $parentParentPage->setUri('parentParent');

        $parentParentParentPage = new Uri();
        $parentParentParentPage->setVisible(true);
        $parentParentParentPage->setActive(true);
        $parentParentParentPage->setUri('parentParentParent');

        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $container = new \Mimmi20\Mezzio\Navigation\Navigation();
        $container->addPage($parentParentParentPage);

        $role     = 'testRole';
        $maxDepth = -1;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setMinDepth(-1);
        $helper->setMaxDepth($maxDepth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name));
    }

    /** @throws Exception */
    public function testInvoke(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($container)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(\Mimmi20\Mezzio\Navigation\Navigation::class, $container1);
        self::assertTrue($helper->hasContainer());

        $helper($container);

        self::assertSame($container, $helper->getContainer());
        self::assertTrue($helper->hasContainer());
    }

    /** @throws Exception */
    public function testToStringExceptionInPluginManager(): void
    {
        $proxy = 'menu';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::once())
            ->method('error')
            ->with(new IsInstanceOf(RuntimeException::class));
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

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

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertSame('', (string) $helper);
    }

    /** @throws Exception */
    public function testToStringExceptionInRenderer(): void
    {
        $proxy     = 'menu';
        $container = null;
        $auth      = $this->createMock(AuthorizationInterface::class);
        $exception = new RuntimeException('test');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::once())
            ->method('error')
            ->with($exception);
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $menu    = $this->getMockBuilder(Navigation\ViewHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher = self::exactly(2);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function ($container = null) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertNull($container),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                        ),
                    };
                },
            );
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with($auth);
        $menu->expects(self::once())
            ->method('hasRole')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('render')
            ->with($container)
            ->willThrowException($exception);

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

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame('', (string) $helper);
    }

    /** @throws Exception */
    public function testToString(): void
    {
        $proxy     = 'menu';
        $container = null;
        $rendered  = '';
        $auth      = $this->createMock(AuthorizationInterface::class);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emergency');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('critical');
        $logger->expects(self::never())
            ->method('error');
        $logger->expects(self::never())
            ->method('warning');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
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

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $logger, $htmlify, $containerParser);

        $menu    = $this->getMockBuilder(Navigation\ViewHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher = self::exactly(2);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function ($container = null) use ($matcher): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertNull($container),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                        ),
                    };
                },
            );
        $menu->expects(self::once())
            ->method('hasAuthorization')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setAuthorization')
            ->with($auth);
        $menu->expects(self::once())
            ->method('hasRole')
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

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame($rendered, (string) $helper($container));
    }
}
