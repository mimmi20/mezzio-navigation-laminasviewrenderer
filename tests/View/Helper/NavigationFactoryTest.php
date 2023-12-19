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

use AssertionError;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\NavigationFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class NavigationFactoryTest extends TestCase
{
    private NavigationFactory $factory;

    /** @throws void */
    protected function setUp(): void
    {
        $this->factory = new NavigationFactory();
    }

    /** @throws Exception */
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

    /** @throws Exception */
    public function testInvocation(): void
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

        $htmlify                 = $this->createMock(HtmlifyInterface::class);
        $containerParser         = $this->createMock(ContainerParserInterface::class);
        $navigationPluginManager = $this->createMock(ViewHelperPluginManager::class);

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher   = self::exactly(4);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $logger, $htmlify, $containerParser, $navigationPluginManager): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(LoggerInterface::class, $id),
                        2 => self::assertSame(HtmlifyInterface::class, $id),
                        3 => self::assertSame(ContainerParserInterface::class, $id),
                        default => self::assertSame(Navigation\PluginManager::class, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $logger,
                        2 => $htmlify,
                        3 => $containerParser,
                        default => $navigationPluginManager,
                    };
                },
            );

        $navigation = ($this->factory)($container);

        self::assertInstanceOf(Navigation::class, $navigation);

        self::assertSame($navigationPluginManager, $navigation->getPluginManager());
    }
}
