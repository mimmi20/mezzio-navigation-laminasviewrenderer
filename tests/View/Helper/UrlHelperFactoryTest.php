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

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper;

use Mezzio\Helper\Exception\MissingHelperException;
use Mezzio\Helper\UrlHelper as BaseUrlHelper;
use Mezzio\LaminasView\UrlHelper;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\UrlHelperFactory;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function sprintf;

final class UrlHelperFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvocationException(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with(BaseUrlHelper::class)
            ->willReturn(false);

        $this->expectException(MissingHelperException::class);
        $this->expectExceptionMessage(
            sprintf(
                'An instance of %s is required in order to create the "url" view helper; not found',
                BaseUrlHelper::class,
            ),
        );
        $this->expectExceptionCode(0);

        assert($container instanceof ContainerInterface);
        (new UrlHelperFactory())($container);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvocation(): void
    {
        $baseHelper = $this->createMock(BaseUrlHelper::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with(BaseUrlHelper::class)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with(BaseUrlHelper::class)
            ->willReturn($baseHelper);

        assert($container instanceof ContainerInterface);
        $urlHelper = (new UrlHelperFactory())($container);

        self::assertInstanceOf(UrlHelper::class, $urlHelper);
    }
}
