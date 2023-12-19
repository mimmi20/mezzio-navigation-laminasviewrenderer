<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\PluginManager;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\PluginManagerFactory;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;

final class PluginManagerFactoryTest extends TestCase
{
    private PluginManagerFactory $factory;

    /** @throws void */
    protected function setUp(): void
    {
        $this->factory = new PluginManagerFactory();
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationHasServiceListener(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with('ServiceListener')
            ->willReturn(true);
        $container->expects(self::never())
            ->method('get');

        assert($container instanceof ContainerInterface);
        $result = ($this->factory)($container, '');

        self::assertInstanceOf(PluginManager::class, $result);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationHasNoServiceListenerAndNoConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher   = self::exactly(2);
        $container->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('ServiceListener', $id),
                        default => self::assertSame('config', $id),
                    };

                    return false;
                },
            );
        $container->expects(self::never())
            ->method('get');

        assert($container instanceof ContainerInterface);
        $result = ($this->factory)($container, '');

        self::assertInstanceOf(PluginManager::class, $result);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationHasNoServiceListenerButAnEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher   = self::exactly(2);
        $container->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('ServiceListener', $id),
                        default => self::assertSame('config', $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => false,
                        default => true,
                    };
                },
            );
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn([]);

        assert($container instanceof ContainerInterface);
        $result = ($this->factory)($container, '');

        self::assertInstanceOf(PluginManager::class, $result);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocationHasNoServiceListenerButAConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher   = self::exactly(2);
        $container->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('ServiceListener', $id),
                        default => self::assertSame('config', $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => false,
                        default => true,
                    };
                },
            );
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(['navigation_helpers' => []]);

        assert($container instanceof ContainerInterface);
        $result = ($this->factory)($container, '');

        self::assertInstanceOf(PluginManager::class, $result);
    }
}
