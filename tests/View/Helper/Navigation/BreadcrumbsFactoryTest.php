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

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use AssertionError;
use Laminas\I18n\View\Helper\Translate;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\BreadcrumbsFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class BreadcrumbsFactoryTest extends TestCase
{
    private BreadcrumbsFactory $factory;

    /** @throws void */
    protected function setUp(): void
    {
        $this->factory = new BreadcrumbsFactory();
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationWithTranslator(): void
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

        $htmlify         = $this->createMock(HtmlifyInterface::class);
        $containerParser = $this->createMock(ContainerParserInterface::class);
        $translatePlugin = $this->createMock(Translate::class);
        $escapePlugin    = $this->createMock(EscapeHtml::class);
        $renderer        = $this->createMock(PartialRendererInterface::class);

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperPluginManager->expects(self::once())
            ->method('has')
            ->with(Translate::class)
            ->willReturn(true);
        $matcher = self::exactly(2);
        $viewHelperPluginManager->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $translatePlugin, $escapePlugin): HelperInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(Translate::class, $name),
                        default => self::assertSame(EscapeHtml::class, $name),
                    };

                    self::assertNull($options);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $translatePlugin,
                        default => $escapePlugin,
                    };
                },
            );

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher   = self::exactly(5);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $viewHelperPluginManager, $logger, $htmlify, $containerParser, $renderer): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(ViewHelperPluginManager::class, $id),
                        2 => self::assertSame(LoggerInterface::class, $id),
                        3 => self::assertSame(HtmlifyInterface::class, $id),
                        4 => self::assertSame(ContainerParserInterface::class, $id),
                        default => self::assertSame(PartialRendererInterface::class, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $viewHelperPluginManager,
                        2 => $logger,
                        3 => $htmlify,
                        4 => $containerParser,
                        default => $renderer,
                    };
                },
            );

        $helper = ($this->factory)($container);

        self::assertInstanceOf(Breadcrumbs::class, $helper);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationWithoutTranslator(): void
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

        $htmlify         = $this->createMock(HtmlifyInterface::class);
        $containerParser = $this->createMock(ContainerParserInterface::class);
        $escapePlugin    = $this->createMock(EscapeHtml::class);
        $renderer        = $this->createMock(PartialRendererInterface::class);

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperPluginManager->expects(self::once())
            ->method('has')
            ->with(Translate::class)
            ->willReturn(false);
        $viewHelperPluginManager->expects(self::once())
            ->method('get')
            ->with(EscapeHtml::class)
            ->willReturn($escapePlugin);

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher   = self::exactly(5);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $viewHelperPluginManager, $logger, $htmlify, $containerParser, $renderer): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(ViewHelperPluginManager::class, $id),
                        2 => self::assertSame(LoggerInterface::class, $id),
                        3 => self::assertSame(HtmlifyInterface::class, $id),
                        4 => self::assertSame(ContainerParserInterface::class, $id),
                        default => self::assertSame(PartialRendererInterface::class, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $viewHelperPluginManager,
                        2 => $logger,
                        3 => $htmlify,
                        4 => $containerParser,
                        default => $renderer,
                    };
                },
            );

        $helper = ($this->factory)($container);

        self::assertInstanceOf(Breadcrumbs::class, $helper);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationWithAssertionError(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('assert($container instanceof ServiceLocatorInterface)');

        ($this->factory)($container);
    }
}
