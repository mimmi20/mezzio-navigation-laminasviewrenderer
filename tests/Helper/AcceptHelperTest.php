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

use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\LaminasView\Helper\AcceptHelper;
use Mezzio\Navigation\Page\PageInterface;
use PHPUnit\Framework\TestCase;

final class AcceptHelperTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testDoNotAcceptInvisiblePages(): void
    {
        $role = 'testRole';
        $auth = $this->createMock(AuthorizationInterface::class);

        /** @var AuthorizationInterface $auth */
        $helper = new AcceptHelper($auth, false, $role);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');

        /* @var PageInterface $page */
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testDoNotAcceptByAuthorization(): void
    {
        $role      = 'testRole';
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(false);

        /** @var AuthorizationInterface $auth */
        $helper = new AcceptHelper($auth, false, $role);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);

        /* @var PageInterface $page */
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testDoNotAcceptByAuthorizationWithParent(): void
    {
        $role      = 'testRole';
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /** @var AuthorizationInterface $auth */
        $helper = new AcceptHelper($auth, false, $role);

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);

        /* @var PageInterface $page */
        self::assertFalse($helper->accept($page));
    }
}
