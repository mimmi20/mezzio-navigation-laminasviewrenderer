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

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use AssertionError;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\HeadLink;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\LinksFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindRoot\FindRootInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class LinksFactoryTest extends TestCase
{
    private LinksFactory $factory;

    /** @throws void */
    protected function setUp(): void
    {
        $this->factory = new LinksFactory();
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
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

        $htmlify         = $this->createMock(HtmlifyInterface::class);
        $rootFinder      = $this->createMock(FindRootInterface::class);
        $containerParser = $this->createMock(ContainerParserInterface::class);
        $headLink        = $this->createMock(HeadLink::class);

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperPluginManager->expects(self::once())
            ->method('get')
            ->with(HeadLink::class)
            ->willReturn($headLink);

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher   = self::exactly(5);
        $container->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $viewHelperPluginManager, $logger, $htmlify, $containerParser, $rootFinder): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(ViewHelperPluginManager::class, $id),
                        2 => self::assertSame(LoggerInterface::class, $id),
                        3 => self::assertSame(HtmlifyInterface::class, $id),
                        4 => self::assertSame(ContainerParserInterface::class, $id),
                        default => self::assertSame(FindRootInterface::class, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $viewHelperPluginManager,
                        2 => $logger,
                        3 => $htmlify,
                        4 => $containerParser,
                        default => $rootFinder,
                    };
                },
            );

        $helper = ($this->factory)($container);

        self::assertInstanceOf(Links::class, $helper);
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
