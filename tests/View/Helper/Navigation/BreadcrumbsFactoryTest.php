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
use Laminas\I18n\View\Helper\Translate;
use Laminas\Log\Logger;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\Partial;
use Laminas\View\HelperPluginManager;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
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
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testInvocationWithTranslator(): void
    {
        $logger          = $this->createMock(Logger::class);
        $htmlify         = $this->createMock(HtmlifyInterface::class);
        $translatePlugin = $this->createMock(Translate::class);
        $escapePlugin    = $this->createMock(EscapeHtml::class);
        $partialPlugin   = $this->createMock(Partial::class);

        $helperPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('has')
            ->with(Translate::class)
            ->willReturn(true);
        $helperPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([Translate::class], [EscapeHtml::class], [Partial::class])
            ->willReturnOnConsecutiveCalls($translatePlugin, $escapePlugin, $partialPlugin);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([HelperPluginManager::class], [Logger::class], [HtmlifyInterface::class])
            ->willReturnOnConsecutiveCalls($helperPluginManager, $logger, $htmlify);

        /** @var ContainerInterface $container */
        $helper = ($this->factory)($container);

        self::assertInstanceOf(Breadcrumbs::class, $helper);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testInvocationWithoutTranslator(): void
    {
        $logger        = $this->createMock(Logger::class);
        $htmlify       = $this->createMock(HtmlifyInterface::class);
        $escapePlugin  = $this->createMock(EscapeHtml::class);
        $partialPlugin = $this->createMock(Partial::class);

        $helperPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('has')
            ->with(Translate::class)
            ->willReturn(false);
        $helperPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([EscapeHtml::class], [Partial::class])
            ->willReturnOnConsecutiveCalls($escapePlugin, $partialPlugin);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([HelperPluginManager::class], [Logger::class], [HtmlifyInterface::class])
            ->willReturnOnConsecutiveCalls($helperPluginManager, $logger, $htmlify);

        /** @var ContainerInterface $container */
        $helper = ($this->factory)($container);

        self::assertInstanceOf(Breadcrumbs::class, $helper);
    }
}
