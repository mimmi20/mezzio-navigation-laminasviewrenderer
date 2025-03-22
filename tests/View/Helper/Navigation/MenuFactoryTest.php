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

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use AssertionError;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\MenuFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class MenuFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvocation(): void
    {
        $htmlify         = $this->createMock(HtmlifyInterface::class);
        $containerParser = $this->createMock(ContainerParserInterface::class);
        $escapePlugin    = $this->createMock(EscapeHtmlAttr::class);
        $renderer        = $this->createMock(PartialRendererInterface::class);

        $viewHelperPluginManager = $this->createMock(ViewHelperPluginManager::class);
        $viewHelperPluginManager->expects(self::once())
            ->method('get')
            ->with(EscapeHtmlAttr::class)
            ->willReturn($escapePlugin);

        $container = $this->createMock(ServiceLocatorInterface::class);
        $matcher   = self::exactly(4);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $viewHelperPluginManager, $htmlify, $containerParser, $renderer): mixed {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            ViewHelperPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                        2 => self::assertSame(HtmlifyInterface::class, $id, (string) $invocation),
                        3 => self::assertSame(
                            ContainerParserInterface::class,
                            $id,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            PartialRendererInterface::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $viewHelperPluginManager,
                        2 => $htmlify,
                        3 => $containerParser,
                        default => $renderer,
                    };
                },
            );

        $helper = (new MenuFactory())($container);

        self::assertInstanceOf(Menu::class, $helper);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvocationWithAssertionError(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('get');

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('assert($container instanceof ServiceLocatorInterface)');

        (new MenuFactory())($container);
    }
}
