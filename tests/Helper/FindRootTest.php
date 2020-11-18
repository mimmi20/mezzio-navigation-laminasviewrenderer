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

use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\LaminasView\Helper\FindRoot;
use Mezzio\Navigation\Page\PageInterface;
use PHPUnit\Framework\TestCase;

final class FindRootTest extends TestCase
{
    /** @var FindRoot */
    private $findRoot;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->findRoot = new FindRoot();
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRoot(): void
    {
        $root = $this->createMock(ContainerInterface::class);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('getParent');

        /* @var ContainerInterface $root */
        $this->findRoot->setRoot($root);

        /* @var PageInterface $page */
        self::assertSame($root, $this->findRoot->find($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testFindRootRecursive(): void
    {
        $root = $this->createMock(ContainerInterface::class);
        \assert($root instanceof ContainerInterface);

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('getParent')
            ->willReturn($root);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);

        /* @var PageInterface $page */
        self::assertSame($root, $this->findRoot->find($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testFindRootWithoutParent(): void
    {
        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn(null);

        /* @var PageInterface $page */
        self::assertSame($page, $this->findRoot->find($page));
    }
}
