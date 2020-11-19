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
namespace MezzioTest\Navigation\LaminasView\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Mezzio\Navigation\LaminasView\Helper\FindRootInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\LinksFactory;
use PHPUnit\Framework\TestCase;

final class LinksFactoryTest extends TestCase
{
    /** @var LinksFactory */
    private $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->factory = new LinksFactory();
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
        $logger     = $this->createMock(Logger::class);
        $htmlify    = $this->createMock(HtmlifyInterface::class);
        $rootFinder = $this->createMock(FindRootInterface::class);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([Logger::class], [HtmlifyInterface::class], [FindRootInterface::class])
            ->willReturnOnConsecutiveCalls($logger, $htmlify, $rootFinder);

        /** @var ContainerInterface $container */
        $helper = ($this->factory)($container);

        self::assertInstanceOf(Links::class, $helper);
    }
}
