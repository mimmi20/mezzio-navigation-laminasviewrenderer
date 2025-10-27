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
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\LaminasView\ServerUrlHelper;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\SitemapFactory;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class SitemapFactoryTest extends TestCase
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
        $basePath        = $this->createMock(BasePath::class);
        $escaper         = $this->createMock(EscapeHtml::class);
        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);

        $viewHelperPluginManager = $this->createMock(ViewHelperPluginManager::class);
        $matcher                 = self::exactly(3);
        $viewHelperPluginManager->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $serverUrlHelper, $basePath, $escaper): HelperInterface {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(ServerUrlHelper::class, $name, (string) $invocation),
                        2 => self::assertSame(BasePath::class, $name, (string) $invocation),
                        default => self::assertSame(EscapeHtml::class, $name, (string) $invocation),
                    };

                    self::assertNull($options, (string) $invocation);

                    return match ($invocation) {
                        1 => $serverUrlHelper,
                        2 => $basePath,
                        default => $escaper,
                    };
                },
            );

        $container = $this->createMock(ServiceLocatorInterface::class);
        $matcher   = self::exactly(3);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $viewHelperPluginManager, $htmlify, $containerParser): mixed {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(
                            ViewHelperPluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                        2 => self::assertSame(HtmlifyInterface::class, $id, (string) $invocation),
                        default => self::assertSame(
                            ContainerParserInterface::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $viewHelperPluginManager,
                        2 => $htmlify,
                        default => $containerParser,
                    };
                },
            );

        $helper = (new SitemapFactory())($container);

        self::assertInstanceOf(Sitemap::class, $helper);
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

        (new SitemapFactory())($container);
    }
}
