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
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\LaminasView\Helper\AcceptHelper;
use Mezzio\Navigation\LaminasView\Helper\AcceptHelperFactory;
use PHPUnit\Framework\TestCase;

final class AcceptHelperFactoryTest extends TestCase
{
    /** @var AcceptHelperFactory */
    private $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->factory = new AcceptHelperFactory();
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
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)($container, '');

        self::assertInstanceOf(AcceptHelper::class, $helper);

        self::assertNull($helper->getAuthorization());
        self::assertNull($helper->getRole());
        self::assertFalse($helper->getRenderInvisible());
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
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        $auth            = $this->createMock(AuthorizationInterface::class);
        $renderInvisible = true;
        $role            = 'test-role';

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)(
            $container,
            '',
            [
                'authorization' => $auth,
                'renderInvisible' => $renderInvisible,
                'role' => $role,
            ]
        );

        self::assertInstanceOf(AcceptHelper::class, $helper);

        self::assertSame($auth, $helper->getAuthorization());
        self::assertSame($role, $helper->getRole());
        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testInvocationWithOptions2(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        $auth            = 'invalid-auth';
        $renderInvisible = '1';
        $role            = null;

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)(
            $container,
            '',
            [
                'authorization' => $auth,
                'renderInvisible' => $renderInvisible,
                'role' => $role,
            ]
        );

        self::assertInstanceOf(AcceptHelper::class, $helper);

        self::assertNull($helper->getAuthorization());
        self::assertNull($helper->getRole());
        self::assertTrue($helper->getRenderInvisible());
    }
}
