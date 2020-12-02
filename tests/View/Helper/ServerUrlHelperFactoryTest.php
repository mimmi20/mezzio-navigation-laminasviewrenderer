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
namespace MezzioTest\Navigation\LaminasView\View\Helper;

use Interop\Container\ContainerInterface;
use Mezzio\Helper\Exception\MissingHelperException;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\Navigation\LaminasView\View\Helper\ServerUrlHelperFactory;
use PHPUnit\Framework\TestCase;

final class ServerUrlHelperFactoryTest extends TestCase
{
    /** @var ServerUrlHelperFactory */
    private $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->factory = new ServerUrlHelperFactory();
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     *
     * @return void
     */
    public function testInvocationException(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with(BaseServerUrlHelper::class)
            ->willReturn(false);

        $this->expectException(MissingHelperException::class);
        $this->expectExceptionMessage(
            sprintf(
                'An instance of %s is required in order to create the "url" view helper; not found',
                BaseServerUrlHelper::class
            )
        );

        /* @var ContainerInterface $container */
        ($this->factory)($container);
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
        $baseHelper = $this->createMock(BaseServerUrlHelper::class);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with(BaseServerUrlHelper::class)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with(BaseServerUrlHelper::class)
            ->willReturn($baseHelper);

        /** @var ContainerInterface $container */
        $serverUrlHelper = ($this->factory)($container);

        self::assertInstanceOf(ServerUrlHelper::class, $serverUrlHelper);
    }
}
