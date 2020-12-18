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
use Laminas\ServiceManager\PluginManagerInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\LaminasView\Helper\AcceptHelperInterface;
use Mezzio\Navigation\LaminasView\Helper\FindActive;
use Mezzio\Navigation\LaminasView\Helper\FindActiveFactory;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;
use PHPUnit\Framework\TestCase;

final class FindActiveFactoryTest extends TestCase
{
    /** @var FindActiveFactory */
    private $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->factory = new FindActiveFactory();
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testInvocationWithoutOptions(): void
    {
        $options = [
            'authorization' => null,
            'renderInvisible' => false,
            'role' => null,
        ];

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::never())
            ->method('accept');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::never())
            ->method('get');
        $helperPluginManager->expects(self::never())
            ->method('has');
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(AcceptHelperInterface::class, $options)
            ->willReturn($acceptHelper);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with(HelperPluginManager::class)
            ->willReturn($helperPluginManager);

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)($container, '');

        self::assertInstanceOf(FindActive::class, $helper);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testInvocationWithOptions(): void
    {
        $auth            = $this->createMock(AuthorizationInterface::class);
        $renderInvisible = true;
        $role            = 'test-role';

        $options = [
            'authorization' => $auth,
            'renderInvisible' => $renderInvisible,
            'role' => $role,
        ];

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::never())
            ->method('accept');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::never())
            ->method('get');
        $helperPluginManager->expects(self::never())
            ->method('has');
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(AcceptHelperInterface::class, $options)
            ->willReturn($acceptHelper);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with(HelperPluginManager::class)
            ->willReturn($helperPluginManager);

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)(
            $container,
            '',
            $options
        );

        self::assertInstanceOf(FindActive::class, $helper);
    }
}