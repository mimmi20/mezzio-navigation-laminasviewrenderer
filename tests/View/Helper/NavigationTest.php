<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper;

use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\Mezzio\Navigation\Page\Uri;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function assert;
use function sprintf;

final class NavigationTest extends TestCase
{
    /** @throws void */
    #[Override]
    protected function tearDown(): void
    {
        Navigation::setDefaultAuthorization(null);
        Navigation::setDefaultRole(null);
    }

    /** @throws Exception */
    public function testSetPluginManager(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $pluginManager = $this->createMock(HelperPluginManager::class);
        $pluginManager->expects(self::once())
            ->method('setRenderer')
            ->with($view);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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
        $view = $this->createMock(RendererInterface::class);

        $pluginManager = $this->createMock(HelperPluginManager::class);
        $pluginManager->expects(self::once())
            ->method('setRenderer')
            ->with($view);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        self::assertTrue($helper->getInjectAuthorization());

        $helper->setInjectAuthorization(false);

        self::assertFalse($helper->getInjectAuthorization());

        $helper->setInjectAuthorization();

        self::assertTrue($helper->getInjectAuthorization());
    }

    /** @throws Exception */
    public function testSetDefaultProxy(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        self::assertSame('menu', $helper->getDefaultProxy());

        $helper->setDefaultProxy('links');

        self::assertSame('links', $helper->getDefaultProxy());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testFindHelperWithoutPluginManager(): void
    {
        $proxy = 'menu';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testFindHelperNotInPluginManager(): void
    {
        $proxy = 'menu';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $pluginManager = $this->createMock(HelperPluginManager::class);
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testFindHelperNotInPluginManager2(): void
    {
        $proxy = 'menu';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $pluginManager = $this->createMock(HelperPluginManager::class);
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testFindHelperExceptionInPluginManager(): void
    {
        $proxy     = 'menu';
        $exception = new ServiceNotFoundException('test');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $pluginManager = $this->createMock(HelperPluginManager::class);
        $pluginManager->expects(self::once())
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::once())
            ->method('get')
            ->with($proxy)
            ->willThrowException($exception);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to load plugin for %s', $proxy));
        $this->expectExceptionCode(0);

        $helper->findHelper($proxy, false);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testFindHelperExceptionInPluginManager4(): void
    {
        $proxy     = 'menu';
        $exception = new InvalidServiceException('test');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $pluginManager = $this->createMock(HelperPluginManager::class);
        $pluginManager->expects(self::once())
            ->method('has')
            ->with($proxy)
            ->willReturn(true);
        $pluginManager->expects(self::once())
            ->method('get')
            ->with($proxy)
            ->willThrowException($exception);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to load plugin for %s', $proxy));
        $this->expectExceptionCode(0);

        $helper->findHelper($proxy);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testFindHelper(): void
    {
        $proxy = 'menu';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $container1 = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $container2 = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($container1)
            ->willReturn($container2);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
        $matcher = self::exactly(4);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function (\Mimmi20\Mezzio\Navigation\ContainerInterface | string | null $container = null) use ($matcher, $container2): void {
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

        $pluginManager = $this->createMock(HelperPluginManager::class);
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testFindHelperWithRule(): void
    {
        $role  = 'test';
        $proxy = 'menu';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
        $matcher = self::exactly(4);
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
            ->method('setRole')
            ->with($role);

        $pluginManager = $this->createMock(HelperPluginManager::class);
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testRenderExceptionInPluginManager(): void
    {
        $proxy          = 'menu';
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $pluginManager = $this->createMock(HelperPluginManager::class);
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to load plugin for %s', $proxy));
        $this->expectExceptionCode(0);

        self::assertSame('', $helper->render());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testRender(): void
    {
        $proxy     = 'menu';
        $container = null;
        $rendered  = '';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
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
            ->method('render')
            ->with($container)
            ->willReturn($rendered);

        $pluginManager = $this->createMock(HelperPluginManager::class);
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

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testRenderWithException(): void
    {
        $proxy     = 'menu';
        $container = null;

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
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
            ->method('render')
            ->with($container)
            ->willThrowException(new \Laminas\Stdlib\Exception\InvalidArgumentException('test'));

        $pluginManager = $this->createMock(HelperPluginManager::class);
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->render($container);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testRenderWithException2(): void
    {
        $proxy     = 'menu';
        $container = null;

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
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
            ->method('render')
            ->with($container)
            ->willThrowException(new DomainException('test'));

        $pluginManager = $this->createMock(HelperPluginManager::class);
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->render($container);
    }

    /** @throws Exception */
    public function testCallExceptionInPluginManager(): void
    {
        $proxy          = 'menu';
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $pluginManager = $this->createMock(HelperPluginManager::class);
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to load plugin for %s', $proxy));
        $this->expectExceptionCode(0);

        $helper->{$proxy}();
    }

    /** @throws Exception */
    public function testCall(): void
    {
        $proxy     = 'menu';
        $rendered  = '';
        $arguments = null;

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $menu    = $this->createMock(Navigation\MenuInterface::class);
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

        $pluginManager = $this->createMock(HelperPluginManager::class);
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

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /** @throws Exception */
    public function testSetMinDepth(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /** @throws Exception */
    public function testSetRole(): void
    {
        $role        = 'testRole';
        $defaultRole = 'testDefaultRole';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        self::assertNull($helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
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

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        self::assertTrue($helper->getInjectContainer());

        $helper->setInjectContainer(false);

        self::assertFalse($helper->getInjectContainer());

        $helper->setInjectContainer();

        self::assertTrue($helper->getInjectContainer());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $name = 'default';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException(new InvalidArgumentException('test'));

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetContainerWithStringFound(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mimmi20\Mezzio\Navigation\Top';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testDoNotAccept(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mimmi20\Mezzio\Navigation\Top';

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(false);

        $auth = $this->createMock(AuthorizationInterface::class);

        $role = 'testRole';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $helper->setContainer($name);
        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testHtmlify(): void
    {
        $expected = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mimmi20\Mezzio\Navigation\Top';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $page = $this->createMock(PageInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Navigation::class, $page)
            ->willReturn($expected);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $helper->setContainer($name);

        $view = $this->createMock(PhpRenderer::class);
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
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveNoActivePages(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $parentPage = $this->createMock(PageInterface::class);
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

        $page = $this->createMock(PageInterface::class);
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

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveOneActivePage(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $parentPage = $this->createMock(PageInterface::class);
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

        $page = $this->createMock(PageInterface::class);
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

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 0,
                ],
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testFindActiveWithoutContainer(): void
    {
        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with(new IsInstanceOf(\Mimmi20\Mezzio\Navigation\Navigation::class), $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with(null)
            ->willReturn(null);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveOneActivePageWithoutDepth(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $parentPage = $this->createMock(PageInterface::class);
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

        $page = $this->createMock(PageInterface::class);
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

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 0,
                ],
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveOneActivePageOutOfRange(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = $this->createMock(PageInterface::class);
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

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveOneActivePageRecursive(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page = $this->createMock(PageInterface::class);
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

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $parentPage,
                    'depth' => 0,
                ],
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveOneActivePageRecursive2(): void
    {
        $name = 'Mezzio\Navigation\Top';

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

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveOneActivePageRecursive3(): void
    {
        $name = 'Mezzio\Navigation\Top';

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

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setMinDepth(-1);
        $helper->setMaxDepth($maxDepth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($container)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

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

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $pluginManager = $this->createMock(HelperPluginManager::class);
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Failed to load plugin for %s', $proxy));
        $this->expectExceptionCode(0);

        self::assertSame('', (string) $helper);
    }

    /** @throws Exception */
    public function testToStringExceptionInRenderer(): void
    {
        $proxy     = 'menu';
        $container = null;
        $auth      = $this->createMock(AuthorizationInterface::class);
        $exception = new RuntimeException('test');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
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
            ->with($auth);
        $menu->expects(self::once())
            ->method('hasRole')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('render')
            ->with($container)
            ->willThrowException($exception);

        $pluginManager = $this->createMock(HelperPluginManager::class);
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        self::assertSame('', (string) $helper);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testToString(): void
    {
        $proxy     = 'menu';
        $container = null;
        $rendered  = '';
        $auth      = $this->createMock(AuthorizationInterface::class);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation($serviceLocator, $htmlify, $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
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
            ->with($auth);
        $menu->expects(self::once())
            ->method('hasRole')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('render')
            ->with($container)
            ->willReturn($rendered);

        $pluginManager = $this->createMock(HelperPluginManager::class);
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
