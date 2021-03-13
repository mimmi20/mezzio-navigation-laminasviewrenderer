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
use Laminas\I18n\View\Helper\Translate;
use Laminas\Log\Logger;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Helper\PluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\BreadcrumbsFactory;
use PHPUnit\Framework\TestCase;

final class BreadcrumbsFactoryTest extends TestCase
{
    /** @var BreadcrumbsFactory */
    private $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->factory = new BreadcrumbsFactory();
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testInvocationWithTranslator(): void
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
        $translatePlugin = $this->createMock(Translate::class);
        $escapePlugin    = $this->createMock(EscapeHtml::class);
        $renderer        = $this->createMock(LaminasViewRenderer::class);

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
            ->method('has')
            ->with(Translate::class)
            ->willReturn(true);
        $viewHelperPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Translate::class], [EscapeHtml::class])
            ->willReturnOnConsecutiveCalls($translatePlugin, $escapePlugin);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive([PluginManager::class], [ViewHelperPluginManager::class], [Logger::class], [LaminasViewRenderer::class])
            ->willReturnOnConsecutiveCalls($helperPluginManager, $viewHelperPluginManager, $logger, $renderer);

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)($container);

        self::assertInstanceOf(Breadcrumbs::class, $helper);
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testInvocationWithoutTranslator(): void
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
        $escapePlugin    = $this->createMock(EscapeHtml::class);
        $renderer        = $this->createMock(LaminasViewRenderer::class);

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
            ->method('has')
            ->with(Translate::class)
            ->willReturn(false);
        $viewHelperPluginManager->expects(self::once())
            ->method('get')
            ->with(EscapeHtml::class)
            ->willReturn($escapePlugin);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive([PluginManager::class], [ViewHelperPluginManager::class], [Logger::class], [LaminasViewRenderer::class])
            ->willReturnOnConsecutiveCalls($helperPluginManager, $viewHelperPluginManager, $logger, $renderer);

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)($container);

        self::assertInstanceOf(Breadcrumbs::class, $helper);
    }
}
