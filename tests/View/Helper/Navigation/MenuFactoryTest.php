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

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\LaminasViewHelper\Helper\PartialRendererInterface;
use Mezzio\LaminasViewHelper\Helper\PluginManager as LvhPluginManager;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Helper\PluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\MenuFactory;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function assert;

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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([HtmlifyInterface::class], [ContainerParserInterface::class])
            ->willReturn($htmlify, $containerParser);

        $viewHelperPluginManager = $this->getMockBuilder(ViewHelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperPluginManager->expects(self::once())
            ->method('get')
            ->with(EscapeHtmlAttr::class)
            ->willReturn($escapePlugin);

        $lvhPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lvhPluginManager->expects(self::once())
            ->method('get')
            ->with(PartialRendererInterface::class)
            ->willReturn($renderer);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive([PluginManager::class], [ViewHelperPluginManager::class], [LvhPluginManager::class], [Logger::class])
            ->willReturnOnConsecutiveCalls($helperPluginManager, $viewHelperPluginManager, $lvhPluginManager, $logger);

        assert($container instanceof ContainerInterface);
        $helper = ($this->factory)($container);

        self::assertInstanceOf(Menu::class, $helper);
    }
}
