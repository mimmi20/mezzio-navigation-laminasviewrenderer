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
namespace MezzioTest\Navigation\LaminasView\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\View\Exception\InvalidArgumentException;
use Mezzio\Navigation\LaminasView\Helper\ContainerParser;
use Mezzio\Navigation\Navigation;
use PHPUnit\Framework\TestCase;

final class ContainerParserTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testParseContainerWithNull(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        \assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertNull($helper->parseContainer(null));
    }

    /**
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     *
     * @return void
     */
    public function testParseContainerWithNumber(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        \assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Container must be a string alias or an instance of Mezzio\Navigation\ContainerInterface');

        $helper->parseContainer(1);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testParseContainerWithStringDefaultNotFound(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(Navigation::class)
            ->willThrowException(new ServiceNotFoundException('test'));

        \assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', Navigation::class));

        $helper->parseContainer('default');
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testParseContainerWithStringDefaultFound(): void
    {
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(Navigation::class)
            ->willReturn($container);

        \assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer('default'));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testParseContainerWithStringNavigationNotFound(): void
    {
        $name = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        \assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', $name));

        $helper->parseContainer($name);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testParseContainerWithStringNavigationFound(): void
    {
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        \assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($name));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testParseContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $name = 'default';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Navigation::class], ['navigation'])
            ->willReturnOnConsecutiveCalls(false, false);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        \assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', $name));

        $helper->parseContainer($name);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testParseContainerWithStringFound(): void
    {
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        \assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($name));
    }

    /**
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     *
     * @return void
     */
    public function testParseContainerWithContainer(): void
    {
        $container      = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        \assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($container));
    }
}
