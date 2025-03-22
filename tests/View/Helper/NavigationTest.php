<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper;

use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\Stdlib\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

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

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetPluginManager(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $pluginManager = $this->createMock(HelperPluginManager::class);
        $pluginManager->expects(self::once())
            ->method('setRenderer')
            ->with($view);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetPluginManager2(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $pluginManager = $this->createMock(HelperPluginManager::class);
        $pluginManager->expects(self::once())
            ->method('setRenderer')
            ->with($view);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        assert($pluginManager instanceof HelperPluginManager);
        $helper->setPluginManager($pluginManager);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetInjectAuthorization(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        self::assertTrue($helper->getInjectAuthorization());

        $helper->setInjectAuthorization(false);

        self::assertFalse($helper->getInjectAuthorization());

        $helper->setInjectAuthorization();

        self::assertTrue($helper->getInjectAuthorization());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetDefaultProxy(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        self::assertSame('menu', $helper->getDefaultProxy());

        $helper->setDefaultProxy('links');

        self::assertSame('links', $helper->getDefaultProxy());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindHelperWithoutPluginManager(): void
    {
        $proxy = 'menu';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindHelperNotInPluginManager(): void
    {
        $proxy = 'menu';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindHelperNotInPluginManager2(): void
    {
        $proxy = 'menu';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindHelperExceptionInPluginManager(): void
    {
        $proxy     = 'menu';
        $exception = new ServiceNotFoundException('test');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindHelperExceptionInPluginManager4(): void
    {
        $proxy     = 'menu';
        $exception = new InvalidServiceException('test');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindHelper(): void
    {
        $proxy = 'menu';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $container1 = $this->createMock(ContainerInterface::class);
        $container2 = $this->createMock(ContainerInterface::class);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($container1)
            ->willReturn($container2);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
        $matcher = self::exactly(4);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $container = null) use ($matcher, $container2): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertNull($container, (string) $invocation),
                        default => self::assertSame($container2, $container, (string) $invocation),
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
            ->method('hasRoles')
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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindHelperWithRule(): void
    {
        $role  = 'test';
        $proxy = 'menu';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
        $matcher = self::exactly(4);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $container = null) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertNull($container, (string) $invocation),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                            (string) $invocation,
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
            ->method('hasRoles')
            ->willReturn(false);
        $menu->expects(self::once())
            ->method('setRoles')
            ->with([$role]);

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
        $helper->setRoles([$role]);

        self::assertSame($menu, $helper->findHelper($proxy, false));
        self::assertSame($menu, $helper->findHelper($proxy, true));
        self::assertSame($menu, $helper->findHelper($proxy));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRenderExceptionInPluginManager(): void
    {
        $proxy = 'menu';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRender(): void
    {
        $proxy     = 'menu';
        $container = null;
        $rendered  = '';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
        $matcher = self::exactly(2);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $container = null) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertNull($container, (string) $invocation),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                            (string) $invocation,
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
            ->method('hasRoles')
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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRenderWithException(): void
    {
        $proxy     = 'menu';
        $container = null;

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
        $matcher = self::exactly(2);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $container = null) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertNull($container, (string) $invocation),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                            (string) $invocation,
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
            ->method('hasRoles')
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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRenderWithException2(): void
    {
        $proxy     = 'menu';
        $container = null;

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        $menu    = $this->createMock(Navigation\ViewHelperInterface::class);
        $matcher = self::exactly(2);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $container = null) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertNull($container, (string) $invocation),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                            (string) $invocation,
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
            ->method('hasRoles')
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

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCallExceptionInPluginManager(): void
    {
        $proxy = 'menu';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCall(): void
    {
        $proxy     = 'menu';
        $rendered  = '';
        $arguments = null;

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        $menu    = $this->createMock(Navigation\MenuInterface::class);
        $matcher = self::exactly(2);
        $menu->expects($matcher)
            ->method('setContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $container = null) use ($matcher): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertNull($container, (string) $invocation),
                        default => self::assertInstanceOf(
                            \Mimmi20\Mezzio\Navigation\Navigation::class,
                            $container,
                            (string) $invocation,
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
            ->method('hasRoles')
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

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth = 4;

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetMinDepth(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetRenderInvisible(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetRole(): void
    {
        $role        = 'testRole';
        $defaultRole = 'testDefaultRole';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        self::assertSame([], $helper->getRoles());
        self::assertFalse($helper->hasRoles());

        Navigation::setDefaultRole($defaultRole);

        self::assertSame([$defaultRole], $helper->getRoles());
        self::assertTrue($helper->hasRoles());

        $helper->setRoles([$role]);

        self::assertSame([$role], $helper->getRoles());
        self::assertTrue($helper->hasRoles());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetUseAuthorization(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        self::assertFalse($helper->getUseAuthorization());

        $helper->setUseAuthorization();

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetAuthorization(): void
    {
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $defaultAuth = $this->createMock(AuthorizationInterface::class);
        $defaultAuth->expects(self::never())
            ->method('isGranted');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetView(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        self::assertNull($helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | null $containerParam) use ($matcher, $container): ContainerInterface | null {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertNull($containerParam, (string) $invocation),
                        default => self::assertSame($container, $containerParam, (string) $invocation),
                    };

                    return match ($invocation) {
                        1 => null,
                        default => $container,
                    };
                },
            );

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetInjectContainer(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        self::assertTrue($helper->getInjectContainer());

        $helper->setInjectContainer(false);

        self::assertFalse($helper->getInjectContainer());

        $helper->setInjectContainer();

        self::assertTrue($helper->getInjectContainer());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $name = 'default';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException(new InvalidArgumentException('test'));

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetContainerWithStringFound(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mimmi20\Mezzio\Navigation\Top';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testDoNotAccept(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mimmi20\Mezzio\Navigation\Top';

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getLiClass');

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $role = 'testRole';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        $helper->setContainer($name);
        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testHtmlify(): void
    {
        $expected = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mimmi20\Mezzio\Navigation\Top';

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

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

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

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetIndent(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        $helper = new Navigation(htmlify: $htmlify, containerParser: $containerParser);

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }
}
