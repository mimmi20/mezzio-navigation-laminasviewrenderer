<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Helper;

use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\FindRoot;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

final class FindRootTest extends TestCase
{
    /** @throws Exception */
    public function testSetRoot(): void
    {
        $helper = new FindRoot();

        $root = self::createStub(ContainerInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        $helper->setRoot($root);

        self::assertSame($root, $helper->find($page));
    }

    /** @throws Exception */
    public function testFindRootRecursive(): void
    {
        $helper = new FindRoot();

        $root = self::createStub(ContainerInterface::class);

        $parentPage = $this->createMock(PageInterface::class);
        $parentPage->expects(self::once())
            ->method('getParent')
            ->willReturn($root);
        $parentPage->expects(self::never())
            ->method('hashCode');
        $parentPage->expects(self::never())
            ->method('getOrder');
        $parentPage->expects(self::never())
            ->method('setParent');

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertSame($root, $helper->find($page));
    }

    /** @throws Exception */
    public function testFindRootWithoutParent(): void
    {
        $helper = new FindRoot();

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn(value: null);
        $page->expects(self::never())
            ->method('hashCode');
        $page->expects(self::never())
            ->method('getOrder');
        $page->expects(self::never())
            ->method('setParent');

        self::assertSame($page, $helper->find($page));
    }
}
