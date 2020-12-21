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

use Laminas\Config\Config;
use Laminas\Log\Logger;
use Mezzio\Navigation\Exception\InvalidArgumentException;
use Mezzio\Navigation\LaminasView\Helper\ConvertToPages;
use Mezzio\Navigation\Navigation;
use Mezzio\Navigation\Page\PageFactoryInterface;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Uri;
use PHPUnit\Framework\TestCase;

final class ConvertToPagesTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testConvertFromPage(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::never())
            ->method('factory');

        $helper = new ConvertToPages($logger, $pageFactory);

        $page = $this->createMock(PageInterface::class);

        self::assertSame([$page], $helper->convert($page));
        self::assertSame([$page], $helper->convert($page, true));
        self::assertSame([$page], $helper->convert($page, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testConvertFromContainer(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::never())
            ->method('factory');

        $helper = new ConvertToPages($logger, $pageFactory);

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
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testConvertFromString(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
                ]
            )
            ->willReturn($page);

        $helper = new ConvertToPages($logger, $pageFactory);

        self::assertSame([$page], $helper->convert($uri));
        self::assertSame([$page], $helper->convert($uri, true));
        self::assertSame([$page], $helper->convert($uri, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testConvertFromStringWithException(): void
    {
        $exception = new InvalidArgumentException('test');

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::exactly(3))
            ->method('err')
            ->with($exception);
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $uri = 'test-uri';

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::exactly(3))
            ->method('factory')
            ->with(
                [
                    'type' => 'uri',
                    'uri' => $uri,
                ]
            )
            ->willThrowException($exception);

        $helper = new ConvertToPages($logger, $pageFactory);

        self::assertSame([], $helper->convert($uri));
        self::assertSame([], $helper->convert($uri, true));
        self::assertSame([], $helper->convert($uri, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testConvertFromConfig(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $uri  = 'test-uri';
        $page = $this->createMock(PageInterface::class);

        $configArray = [
            'type' => 'uri',
            'uri' => $uri,
        ];
        $config = new Config($configArray);

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::exactly(3))
            ->method('factory')
            ->with($configArray)
            ->willReturn($page);

        $helper = new ConvertToPages($logger, $pageFactory);

        self::assertSame([$page], $helper->convert($config));
        self::assertSame([$page], $helper->convert($config, true));
        self::assertSame([$page], $helper->convert($config, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testConvertFromConfigWithException(): void
    {
        $exception = new InvalidArgumentException('test');

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::exactly(3))
            ->method('err')
            ->with($exception);
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $uri = 'test-uri';

        $configArray = [
            'type' => 'uri',
            'uri' => $uri,
        ];
        $config = new Config($configArray);

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::exactly(3))
            ->method('factory')
            ->with($configArray)
            ->willThrowException($exception);

        $helper = new ConvertToPages($logger, $pageFactory);

        self::assertSame([], $helper->convert($config));
        self::assertSame([], $helper->convert($config, true));
        self::assertSame([], $helper->convert($config, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testConvertFromInteger(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $pageFactory = $this->getMockBuilder(PageFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory->expects(self::never())
            ->method('factory');

        $helper = new ConvertToPages($logger, $pageFactory);

        self::assertSame([], $helper->convert(1));
        self::assertSame([], $helper->convert(1, true));
        self::assertSame([], $helper->convert(1, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testConvertFromArray(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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

        $helper = new ConvertToPages($logger, $pageFactory);

        self::assertSame([$page], $helper->convert($config));
        self::assertSame([$page], $helper->convert($config, true));
        self::assertSame([$page], $helper->convert($config, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testConvertFromArrayWithException(): void
    {
        $exception = new InvalidArgumentException('test');

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::exactly(3))
            ->method('err')
            ->with($exception);
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $uri = 'test-uri';

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
            ->willThrowException($exception);

        $helper = new ConvertToPages($logger, $pageFactory);

        self::assertSame([], $helper->convert($config));
        self::assertSame([], $helper->convert($config, true));
        self::assertSame([], $helper->convert($config, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     *
     * @group Convert
     */
    public function testConvertFromRecursiveArray(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

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
        $pageFactory->expects(self::exactly(5))
            ->method('factory')
            ->withConsecutive([$config1], [$config2], [$config1], [$config2], [$config])
            ->willReturnOnConsecutiveCalls($page1, $page2, $page1, $page2, $page1);

        $helper = new ConvertToPages($logger, $pageFactory);

        self::assertSame([$page1, $page2], $helper->convert($config));
        self::assertSame([$page1, $page2], $helper->convert($config, true));
        self::assertSame([$page1], $helper->convert($config, false));
    }
}
