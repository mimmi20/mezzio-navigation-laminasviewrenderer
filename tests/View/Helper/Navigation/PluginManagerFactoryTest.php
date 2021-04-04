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
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\PluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\PluginManagerFactory;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function assert;

final class PluginManagerFactoryTest extends TestCase
{
    private PluginManagerFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new PluginManagerFactory();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
     */
    public function testInvocationHasNoServiceListenerAndNoConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['ServiceListener'], ['config'])
            ->willReturnOnConsecutiveCalls(false, false);
        $container->expects(self::never())
            ->method('get');

        assert($container instanceof ContainerInterface);
        $result = ($this->factory)($container, '');

        self::assertInstanceOf(PluginManager::class, $result);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvocationHasNoServiceListenerButAnEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['ServiceListener'], ['config'])
            ->willReturnOnConsecutiveCalls(false, true);
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
     * @throws InvalidArgumentException
     */
    public function testInvocationHasNoServiceListenerButAConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['ServiceListener'], ['config'])
            ->willReturnOnConsecutiveCalls(false, true);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(['navigation_helpers' => []]);

        assert($container instanceof ContainerInterface);
        $result = ($this->factory)($container, '');

        self::assertInstanceOf(PluginManager::class, $result);
    }
}
