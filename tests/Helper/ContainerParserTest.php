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

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Helper;

use AssertionError;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ContainerParser;
use Mimmi20\Mezzio\Navigation\Navigation as MezzioNavigation;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function assert;
use function sprintf;

final class ContainerParserTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
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

        $helper = new ContainerParser($serviceLocator);

        self::assertNull($helper->parseContainer());
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
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

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Container must be a string alias or an instance of %s',
                \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
            ),
        );
        $this->expectExceptionCode(0);

        $helper->parseContainer(1);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultNotFound(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(MezzioNavigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(MezzioNavigation::class)
            ->willThrowException(new ServiceNotFoundException('test'));

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Could not load Container "%s"', MezzioNavigation::class),
        );
        $this->expectExceptionCode(0);

        $helper->parseContainer('default');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testParseContainerWithStringDefaultFound(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(MezzioNavigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(MezzioNavigation::class)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer('default'));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testParseContainerWithStringFound(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mimmi20\Mezzio\Navigation\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($name));
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testParseContainerWithContainer(): void
    {
        $container      = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($container));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultNotFound3(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(MezzioNavigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(MezzioNavigation::class)
            ->willReturn(null);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            sprintf(
                '$container should be an Instance of %s, but was %s',
                \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
                'null',
            ),
        );

        $helper->parseContainer('default');
    }
}
