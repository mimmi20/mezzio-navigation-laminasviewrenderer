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

use AssertionError;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\NavigationFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class NavigationFactoryTest extends TestCase
{
    private NavigationFactory $factory;

    /** @throws void */
    #[Override]
    protected function setUp(): void
    {
        $this->factory = new NavigationFactory();
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationWithAssertionError(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())
            ->method('get');

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('assert($container instanceof ServiceLocatorInterface)');

        ($this->factory)($container);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocation(): void
    {
        $htmlify                 = $this->createMock(HtmlifyInterface::class);
        $containerParser         = $this->createMock(ContainerParserInterface::class);
        $navigationPluginManager = $this->createMock(ViewHelperPluginManager::class);

        $container = $this->createMock(ServiceLocatorInterface::class);
        $matcher   = self::exactly(3);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $htmlify, $containerParser, $navigationPluginManager): mixed {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame(HtmlifyInterface::class, $id, (string) $invocation),
                        2 => self::assertSame(
                            ContainerParserInterface::class,
                            $id,
                            (string) $invocation,
                        ),
                        default => self::assertSame(
                            Navigation\PluginManager::class,
                            $id,
                            (string) $invocation,
                        ),
                    };

                    return match ($invocation) {
                        1 => $htmlify,
                        2 => $containerParser,
                        default => $navigationPluginManager,
                    };
                },
            );

        $navigation = ($this->factory)($container);

        self::assertInstanceOf(Navigation::class, $navigation);

        self::assertSame($navigationPluginManager, $navigation->getPluginManager());
    }
}
