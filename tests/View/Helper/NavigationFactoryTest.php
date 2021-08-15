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

namespace MezzioTest\Navigation\LaminasView\View\Helper;

use AssertionError;
use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mezzio\Navigation\LaminasView\View\Helper\NavigationFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class NavigationFactoryTest extends TestCase
{
    private NavigationFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new NavigationFactory();
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

        $htmlify                 = $this->createMock(HtmlifyInterface::class);
        $containerParser         = $this->createMock(ContainerParserInterface::class);
        $navigationPluginManager = $this->createMock(ViewHelperPluginManager::class);

        $container = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive([Logger::class], [HtmlifyInterface::class], [ContainerParserInterface::class], [Navigation\PluginManager::class])
            ->willReturnOnConsecutiveCalls($logger, $htmlify, $containerParser, $navigationPluginManager);

        $navigation = ($this->factory)($container);

        self::assertInstanceOf(Navigation::class, $navigation);

        self::assertSame($navigationPluginManager, $navigation->getPluginManager());
    }
}
