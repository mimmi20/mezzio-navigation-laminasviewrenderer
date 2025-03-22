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
use Laminas\View\Helper\HeadLink;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\LinksFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\ConvertToPages\ConvertToPagesInterface;
use Mimmi20\NavigationHelper\FindRoot\FindRootInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class LinksFactoryTest extends TestCase
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
        $rootFinder      = $this->createMock(FindRootInterface::class);
        $containerParser = $this->createMock(ContainerParserInterface::class);
        $headLink        = $this->createMock(HeadLink::class);
        $converter       = $this->createMock(ConvertToPagesInterface::class);

        $viewHelperPluginManager = $this->createMock(ViewHelperPluginManager::class);
        $viewHelperPluginManager->expects(self::once())
            ->method('get')
            ->with(HeadLink::class)
            ->willReturn($headLink);

        $container = $this->createMock(ServiceLocatorInterface::class);
        $matcher   = self::exactly(5);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $viewHelperPluginManager, $htmlify, $containerParser, $rootFinder, $converter): mixed {
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
                        5 => self::assertSame(
                            ConvertToPagesInterface::class,
                            $id,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            FindRootInterface::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $viewHelperPluginManager,
                        2 => $htmlify,
                        3 => $containerParser,
                        5 => $converter,
                        default => $rootFinder,
                    };
                },
            );

        $helper = (new LinksFactory())($container);

        self::assertInstanceOf(Links::class, $helper);
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

        (new LinksFactory())($container);
    }
}
