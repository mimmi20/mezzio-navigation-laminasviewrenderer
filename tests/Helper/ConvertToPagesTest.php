<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Helper;

use ArrayObject;
use Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ConvertToPages;
use Mimmi20\Mezzio\Navigation\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageFactoryInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\Mezzio\Navigation\Page\Uri;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

final class ConvertToPagesTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConvertFromPage(): void
    {
        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::never())
            ->method('factory');

        $helper = new ConvertToPages($pageFactory);

        $page = $this->createMock(PageInterface::class);

        self::assertSame([$page], $helper->convert($page));
        self::assertSame([$page], $helper->convert($page, true));
        self::assertSame([$page], $helper->convert($page, false));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testConvertFromContainer(): void
    {
        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::never())
            ->method('factory');

        $helper = new ConvertToPages($pageFactory);

        $page1 = new Uri();
        $page2 = new Uri();

        $container = new Navigation();
        $container->addPage($page1);
        $container->addPage($page2);

        self::assertSame([$page1, $page2], $helper->convert($container));
        self::assertSame([$page1, $page2], $helper->convert($container, true));
        self::assertSame([$page1, $page2], $helper->convert($container, false));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConvertFromString(): void
    {
        $uri  = 'test-uri';
        $page = $this->createMock(PageInterface::class);

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::exactly(3))
            ->method('factory')
            ->with(
                [
                    'type' => 'uri',
                    'uri' => $uri,
                ],
            )
            ->willReturn($page);

        $helper = new ConvertToPages($pageFactory);

        self::assertSame([$page], $helper->convert($uri));
        self::assertSame([$page], $helper->convert($uri, true));
        self::assertSame([$page], $helper->convert($uri, false));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testConvertFromStringWithException(): void
    {
        $exception = new InvalidArgumentException('test');

        $uri = 'test-uri';

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::once())
            ->method('factory')
            ->with(
                [
                    'type' => 'uri',
                    'uri' => $uri,
                ],
            )
            ->willThrowException($exception);

        $helper = new ConvertToPages($pageFactory);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->convert($uri);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConvertFromConfig(): void
    {
        $uri  = 'test-uri';
        $page = $this->createMock(PageInterface::class);

        $configArray = [
            'type' => 'uri',
            'uri' => $uri,
        ];
        $config      = new ArrayObject($configArray);

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::exactly(3))
            ->method('factory')
            ->with($configArray)
            ->willReturn($page);

        $helper = new ConvertToPages($pageFactory);

        self::assertSame([$page], $helper->convert($config));
        self::assertSame([$page], $helper->convert($config, true));
        self::assertSame([$page], $helper->convert($config, false));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testConvertFromConfigWithException(): void
    {
        $exception = new InvalidArgumentException('test');

        $uri = 'test-uri';

        $configArray = [
            'type' => 'uri',
            'uri' => $uri,
        ];
        $config      = new ArrayObject($configArray);

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::once())
            ->method('factory')
            ->with($configArray)
            ->willThrowException($exception);

        $helper = new ConvertToPages($pageFactory);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        self::assertSame([], $helper->convert($config));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testConvertFromInteger(): void
    {
        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::never())
            ->method('factory');

        $helper = new ConvertToPages($pageFactory);

        self::assertSame([], $helper->convert(1));
        self::assertSame([], $helper->convert(1, true));
        self::assertSame([], $helper->convert(1, false));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConvertFromArray(): void
    {
        $uri  = 'test-uri';
        $page = $this->createMock(PageInterface::class);

        $config = [
            'type' => 'uri',
            'uri' => $uri,
        ];

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::exactly(3))
            ->method('factory')
            ->with($config)
            ->willReturn($page);

        $helper = new ConvertToPages($pageFactory);

        self::assertSame([$page], $helper->convert($config));
        self::assertSame([$page], $helper->convert($config, true));
        self::assertSame([$page], $helper->convert($config, false));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testConvertFromArrayWithException(): void
    {
        $exception = new InvalidArgumentException('test');

        $uri = 'test-uri';

        $config = [
            'type' => 'uri',
            'uri' => $uri,
        ];

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::once())
            ->method('factory')
            ->with($config)
            ->willThrowException($exception);

        $helper = new ConvertToPages($pageFactory);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        self::assertSame([], $helper->convert($config));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Group('Convert')]
    public function testConvertFromRecursiveArray(): void
    {
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

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher     = self::exactly(5);
        $pageFactory->expects($matcher)
            ->method('factory')
            ->willReturnCallback(
                static function (array $options) use ($matcher, $config1, $config2, $config, $page2, $page1): PageInterface {
                    match ($matcher->numberOfInvocations()) {
                        1, 3 => self::assertSame($config1, $options),
                        2, 4 => self::assertSame($config2, $options),
                        default => self::assertSame($config, $options),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2,4 => $page2,
                        default => $page1,
                    };
                },
            );

        $helper = new ConvertToPages($pageFactory);

        self::assertSame([$page1, $page2], $helper->convert($config));
        self::assertSame([$page1, $page2], $helper->convert($config, true));
        self::assertSame([$page1], $helper->convert($config, false));
    }
}
