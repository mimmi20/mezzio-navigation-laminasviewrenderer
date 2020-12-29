<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace MezzioTest\Navigation\LaminasView\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\View\Helper\HeadLink;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\FindRootInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\LinksFactory;
use PHPUnit\Framework\TestCase;

final class LinksFactoryTest extends TestCase
{
    /** @var LinksFactory */
    private $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->factory = new LinksFactory();
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testInvocation(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(
                [HtmlifyInterface::class],
                [ContainerParserInterface::class],
                [FindRootInterface::class]
            )
            ->willReturnOnConsecutiveCalls(
                $htmlify,
                $containerParser,
                $rootFinder
            );

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperPluginManager->expects(self::once())
            ->method('get')
            ->with(HeadLink::class)
            ->willReturn($headLink);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([HelperPluginManager::class], [ViewHelperPluginManager::class], [Logger::class])
            ->willReturnOnConsecutiveCalls($helperPluginManager, $viewHelperPluginManager, $logger);

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)($container);

        self::assertInstanceOf(Links::class, $helper);
    }
}
