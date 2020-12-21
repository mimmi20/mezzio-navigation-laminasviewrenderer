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
use Laminas\Log\Logger;
use Mezzio\Navigation\LaminasView\Helper\ConvertToPages;
use Mezzio\Navigation\LaminasView\Helper\ConvertToPagesFactory;
use Mezzio\Navigation\Page\PageFactoryInterface;
use PHPUnit\Framework\TestCase;

final class ConvertToPagesFactoryTest extends TestCase
{
    /** @var ConvertToPagesFactory */
    private $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->factory = new ConvertToPagesFactory();
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
        $logger      = $this->createMock(Logger::class);
        $pageFactory = $this->createMock(PageFactoryInterface::class);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Logger::class], [PageFactoryInterface::class])
            ->willReturnOnConsecutiveCalls($logger, $pageFactory);

        \assert($container instanceof ContainerInterface);
        $helper = ($this->factory)($container);

        self::assertInstanceOf(ConvertToPages::class, $helper);
    }
}
