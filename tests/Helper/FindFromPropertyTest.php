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

use Laminas\View\Exception\DomainException;
use Mezzio\Navigation\LaminasView\Helper\AcceptHelperInterface;
use Mezzio\Navigation\LaminasView\Helper\ConvertToPagesInterface;
use Mezzio\Navigation\LaminasView\Helper\FindFromProperty;
use Mezzio\Navigation\Page\PageInterface;
use PHPUnit\Framework\TestCase;

final class FindFromPropertyTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindWrongRelation(): void
    {
        $rel = 'ABC';

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getRel');
        $page->expects(self::never())
            ->method('getRev');

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::never())
            ->method('accept');

        $convertToPages = $this->getMockBuilder(ConvertToPagesInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $convertToPages->expects(self::never())
            ->method('convert');

        $helper = new FindFromProperty($acceptHelper, $convertToPages);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Invalid relation attribute "%s", must be "rel" or "rev"',
                $rel
            )
        );
        $this->expectExceptionCode(0);

        $helper->find($page, $rel, '');
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindNoRelation(): void
    {
        $rel  = 'rel';
        $type = '';

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::once())
            ->method('getRel')
            ->with($type)
            ->willReturn(null);
        $page->expects(self::never())
            ->method('getRev');

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::never())
            ->method('accept');

        $convertToPages = $this->getMockBuilder(ConvertToPagesInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $convertToPages->expects(self::never())
            ->method('convert');

        $helper = new FindFromProperty($acceptHelper, $convertToPages);

        self::assertSame([], $helper->find($page, $rel, $type));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindNoConvertedRelation(): void
    {
        $rel  = 'rel';
        $type = '';
        $uri  = 'test-uri';

        $configArray = [
            'type' => 'uri',
            'uri' => $uri,
        ];

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::once())
            ->method('getRel')
            ->with($type)
            ->willReturn($configArray);
        $page->expects(self::never())
            ->method('getRev');

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::never())
            ->method('accept');

        $convertToPages = $this->getMockBuilder(ConvertToPagesInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $convertToPages->expects(self::once())
            ->method('convert')
            ->with($configArray)
            ->willReturn([]);

        $helper = new FindFromProperty($acceptHelper, $convertToPages);

        self::assertSame([], $helper->find($page, $rel, $type));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindWithConvertedRelation(): void
    {
        $rel  = 'rel';
        $type = '';

        $uri1  = 'test-uri1';
        $uri2  = 'test-uri2';
        $page1 = $this->createMock(PageInterface::class);
        $page2 = $this->createMock(PageInterface::class);

        $config1 = [
            'type' => 'uri',
            'uri' => $uri1,
        ];
        $config2 = [
            'type' => 'uri',
            'uri' => $uri2,
        ];

        $config = [$config1, $config2];

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::once())
            ->method('getRel')
            ->with($type)
            ->willReturn($config);
        $page->expects(self::never())
            ->method('getRev');

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$page1], [$page2])
            ->willReturnOnConsecutiveCalls(false, true);

        $convertToPages = $this->getMockBuilder(ConvertToPagesInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $convertToPages->expects(self::once())
            ->method('convert')
            ->with($config)
            ->willReturn([$page1, $page2]);

        $helper = new FindFromProperty($acceptHelper, $convertToPages);

        self::assertSame([1 => $page2], $helper->find($page, $rel, $type));
    }
}
