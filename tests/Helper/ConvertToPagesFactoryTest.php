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

use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ConvertToPages;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ConvertToPagesFactory;
use Mimmi20\Mezzio\Navigation\Page\PageFactoryInterface;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;

final class ConvertToPagesFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocation(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');
        $container->expects(self::once())
            ->method('has')
            ->with(PageFactoryInterface::class)
            ->willReturn(false);

        assert($container instanceof ContainerInterface);
        $helper = (new ConvertToPagesFactory())($container);

        self::assertInstanceOf(ConvertToPages::class, $helper);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvocation2(): void
    {
        $pageFactory = $this->createMock(PageFactoryInterface::class);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with(PageFactoryInterface::class)
            ->willReturn($pageFactory);
        $container->expects(self::once())
            ->method('has')
            ->with(PageFactoryInterface::class)
            ->willReturn(true);

        assert($container instanceof ContainerInterface);
        $helper = (new ConvertToPagesFactory())($container);

        self::assertInstanceOf(ConvertToPages::class, $helper);
    }
}
