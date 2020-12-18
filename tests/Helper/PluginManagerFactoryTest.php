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
use Mezzio\Navigation\LaminasView\Helper\PluginManager;
use Mezzio\Navigation\LaminasView\Helper\PluginManagerFactory;
use PHPUnit\Framework\TestCase;

final class PluginManagerFactoryTest extends TestCase
{
    /** @var PluginManagerFactory */
    private $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->factory = new PluginManagerFactory();
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
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)($container);

        self::assertInstanceOf(PluginManager::class, $helper);
    }
}