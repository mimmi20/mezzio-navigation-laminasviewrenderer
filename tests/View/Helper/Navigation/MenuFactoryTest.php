<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace MezzioTest\Navigation\LaminasView\View\Helper\Navigation;

use AssertionError;
use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\MenuFactory;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class MenuFactoryTest extends TestCase
{
    private MenuFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new MenuFactory();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
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
        $containerParser = $this->createMock(ContainerParserInterface::class);
        $escapePlugin    = $this->createMock(EscapeHtmlAttr::class);
        $renderer        = $this->createMock(PartialRendererInterface::class);

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperPluginManager->expects(self::once())
            ->method('get')
            ->with(EscapeHtmlAttr::class)
            ->willReturn($escapePlugin);

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(5))
            ->method('get')
            ->withConsecutive([ViewHelperPluginManager::class], [Logger::class], [HtmlifyInterface::class], [ContainerParserInterface::class], [PartialRendererInterface::class])
            ->willReturnOnConsecutiveCalls($viewHelperPluginManager, $logger, $htmlify, $containerParser, $renderer);

        $helper = ($this->factory)($container);

        self::assertInstanceOf(Menu::class, $helper);
    }

    /**
     * @throws Exception
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
