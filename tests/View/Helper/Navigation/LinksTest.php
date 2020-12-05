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
use Laminas\Config\Config;
use Laminas\Log\Logger;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\View\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\LaminasView\Helper\AcceptHelperInterface;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\FindRootInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\Helper\PluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\LinksInterface;
use Mezzio\Navigation\Navigation;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Route;
use Mezzio\Navigation\Page\Uri;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class LinksTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth = 4;
        $logger   = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetMinDepth(): void
    {
        $minDepth = 4;
        $logger   = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth($minDepth);

        self::assertSame($minDepth, $helper->getMinDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetRenderInvisible(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetRole(): void
    {
        $role        = 'testRole';
        $defaultRole = 'testDefaultRole';
        $logger      = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Links::setDefaultRole($defaultRole);

        self::assertSame($defaultRole, $helper->getRole());
        self::assertTrue($helper->hasRole());

        $helper->setRole($role);

        self::assertSame($role, $helper->getRole());
        self::assertTrue($helper->hasRole());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetUseAuthorization(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetAuthorization(): void
    {
        $auth        = $this->createMock(AuthorizationInterface::class);
        $defaultAuth = $this->createMock(AuthorizationInterface::class);
        $logger      = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        /* @var AuthorizationInterface $defaultAuth */
        Links::setDefaultAuthorization($defaultAuth);

        self::assertSame($defaultAuth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($auth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetView(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->getView());

        /* @var RendererInterface $view */
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $logger    = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([null], [$container])
            ->willReturnOnConsecutiveCalls(null, $container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        /* @var AuthorizationInterface $auth */
        $helper->setContainer();

        $container2 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container2);
        self::assertNotSame($container1, $container2);

        $helper->setContainer($container);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $name = 'default';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException(new InvalidArgumentException('test'));

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');

        $helper->setContainer($name);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetContainerWithStringFound(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testDoNotAccept(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(false);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $role = 'testRole';

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setContainer($name);
        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        /* @var PageInterface $page */
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testHtmlify(): void
    {
        $expected = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslatedAndEscaped</a>';
        $logger   = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

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
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Links::class, $page)
            ->willReturn($expected);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setContainer($name);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        /* @var PhpRenderer $view */
        $helper->setView($view);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->htmlify($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetIndent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testFindActiveNoActivePages(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $name = 'Mezzio\\Navigation\\Top';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::never())
            ->method('isVisible');
        $parentPage->expects(self::never())
            ->method('getResource');
        $parentPage->expects(self::never())
            ->method('getPrivilege');
        $parentPage->expects(self::never())
            ->method('getParent');
        $parentPage->expects(self::never())
            ->method('isActive');

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

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(false);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, 0, 42));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testFindActiveOneActivePage(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $name = 'Mezzio\\Navigation\\Top';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::never())
            ->method('isVisible');
        $parentPage->expects(self::never())
            ->method('getResource');
        $parentPage->expects(self::never())
            ->method('getPrivilege');
        $parentPage->expects(self::never())
            ->method('getParent');
        $parentPage->expects(self::never())
            ->method('isActive');

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
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, 0, 42));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testFindActiveWithoutContainer(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with(null)
            ->willReturn(null);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, 0, 42));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testFindActiveOneActivePageWithoutDepth(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $name = 'Mezzio\\Navigation\\Top';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::never())
            ->method('isVisible');
        $parentPage->expects(self::never())
            ->method('getResource');
        $parentPage->expects(self::never())
            ->method('getPrivilege');
        $parentPage->expects(self::never())
            ->method('getParent');
        $parentPage->expects(self::never())
            ->method('isActive');

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
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testFindActiveOneActivePageOutOfRange(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $name = 'Mezzio\\Navigation\\Top';

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

        $container = new Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, 2, 42));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testFindActiveOneActivePageRecursive(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $name = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$page], [$parentPage])
            ->willReturnOnConsecutiveCalls(true, true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::exactly(2))
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::exactly(2))
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $parentPage,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, 0, 0));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSetRenderFlag(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame(LinksInterface::RENDER_ALL, $helper->getRenderFlag());

        $helper->setRenderFlag(LinksInterface::RENDER_ALTERNATE);

        self::assertSame(LinksInterface::RENDER_ALTERNATE, $helper->getRenderFlag());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRevSubsectionWithoutParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn(null);

        /* @var PageInterface $page */
        self::assertNull($helper->searchRevSubsection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRevSubsectionWithParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $parentPage = new Route();

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('hasPage')
            ->with($parentPage)
            ->willReturn(false);

        /* @var PageInterface $page */
        $parentPage->addPage($page);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($parentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRevSubsection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRevSubsectionWithDeepParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $parentPage             = new Route();
        $parentParentPage       = new Route();
        $parentParentParentPage = new Route();

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hasPage');

        /* @var PageInterface $page */
        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($parentParentParentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame($parentPage, $helper->searchRevSubsection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRevSectionWithoutParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn(null);

        /* @var PageInterface $page */
        self::assertNull($helper->searchRevSection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRevSectionWithParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $parentPage = new Route();

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hasPage');

        /* @var PageInterface $page */
        $parentPage->addPage($page);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($parentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRevSection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRevSectionWithDeepParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $parentPage       = new Route();
        $parentParentPage = new Route();

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hasPage');

        /* @var PageInterface $page */
        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($parentParentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame($parentPage, $helper->searchRevSection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelSubsectionWithoutParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('hasPages')
            ->willReturn(false);

        /* @var PageInterface $page */
        self::assertNull($helper->searchRelSubsection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelSubsectionWithParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $parentPage = new Route();
        $page       = new Route();

        $parentPage->addPage($page);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        /* @var PageInterface $page */
        self::assertNull($helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelSubsectionWithDeepParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $page                   = new Route();
        $parentPage             = new Route();
        $parentParentPage       = new Route();
        $parentParentParentPage = new Route();

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentParentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($page, $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelSectionWithoutParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('hasPages')
            ->willReturn(false);

        /* @var PageInterface $page */
        self::assertNull($helper->searchRelSection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelSectionWithParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $parentPage = new Route();
        $page       = new Route();

        $parentPage->addPage($page);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRelSection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelSectionWithDeepParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $page             = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($page, $helper->searchRelSection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelSectionWithDeepParent2(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $page  = new Route();
        $page2 = new Route();
        $page2->setActive(false);
        $page2->setVisible(false);
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page2);
        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$page2], [$page])
            ->willReturnOnConsecutiveCalls(false, true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::exactly(2))
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::exactly(2))
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($page, $helper->searchRelSection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelChapterWithoutParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $page = new Route();

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$page], [$page])
            ->willReturnOnConsecutiveCalls($page, $page);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRelChapter($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelChapterWithParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page       = new Route();

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $parentPage->addPage($page);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$parentPage], [$parentPage])
            ->willReturnOnConsecutiveCalls($parentPage, $parentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($page, $helper->searchRelChapter($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelChapterWithDeepParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $page             = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$parentParentPage], [$parentParentPage])
            ->willReturnOnConsecutiveCalls($parentParentPage, $parentParentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($parentPage, $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelChapterWithDeepParent2(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $page        = new Route();
        $parentPage  = new Route();
        $parentPage2 = new Route();
        $parentPage2->setActive(false);
        $parentPage2->setVisible(false);
        $parentParentPage = new Route();

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage2);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$parentPage2], [$parentPage])
            ->willReturnOnConsecutiveCalls(false, true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::exactly(2))
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::exactly(2))
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$parentParentPage], [$parentParentPage])
            ->willReturnOnConsecutiveCalls($parentParentPage, $parentParentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($parentPage, $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelPrevWithoutParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $page = new Route();

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($page);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRelPrev($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelPrevWithParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$page1], [$page2], [$page2])
            ->willReturnOnConsecutiveCalls(true, true, true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::exactly(3))
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::exactly(3))
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$page1], [$page2])
            ->willReturnOnConsecutiveCalls($parentPage, $parentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertNull($helper->searchRelPrev($page1));
        self::assertSame($page1, $helper->searchRelPrev($page2));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelPrevWithParent2(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();
        $page2->setActive(false);
        $page2->setVisible(false);
        $page3 = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);
        $parentPage->addPage($page3);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(7))
            ->method('accept')
            ->withConsecutive([$page1], [$page1], [$page2], [$page1], [$page1], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls(true, true, false, true, true, false, true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::exactly(7))
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::exactly(7))
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(3))
            ->method('find')
            ->withConsecutive([$page1], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls($parentPage, $parentPage, $parentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertNull($helper->searchRelPrev($page1));
        self::assertNull($helper->searchRelPrev($page2));
        self::assertSame($page1, $helper->searchRelPrev($page3));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelNextWithoutParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $page = new Route();

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($page);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRelNext($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelNextWithParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page1)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$page2], [$page1])
            ->willReturnOnConsecutiveCalls($parentPage, $parentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertNull($helper->searchRelNext($page2));
        self::assertSame($page2, $helper->searchRelNext($page1));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelStartWithoutParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $page = new Route();

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($page);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRelStart($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     *
     * @return void
     */
    public function testSearchRelStartWithParent(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->with($parentPage)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::exactly(2))
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::exactly(2))
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$page1], [$page2])
            ->willReturnOnConsecutiveCalls($parentPage, $parentPage);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($parentPage, $helper->searchRelStart($page1));
        self::assertSame($parentPage, $helper->searchRelStart($page2));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindRelationWithError(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $rel = 'test';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(sprintf('Invalid argument: $rel must be "rel" or "rev"; "%s" given', $rel));

        $helper->findRelation($page1, $rel, 'test');
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindNullRelationFromProperty(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $page1 = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page1->expects(self::never())
            ->method('isVisible');
        $page1->expects(self::never())
            ->method('getResource');
        $page1->expects(self::never())
            ->method('getPrivilege');
        $page1->expects(self::once())
            ->method('getRel')
            ->with($type)
            ->willReturn(1234);
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertNull($helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindPageRelationFromProperty(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $page3 = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page3->expects(self::never())
            ->method('isVisible');
        $page3->expects(self::never())
            ->method('getResource');
        $page3->expects(self::never())
            ->method('getPrivilege');
        $page3->expects(self::never())
            ->method('getRel');
        $page3->expects(self::never())
            ->method('getRev');

        $page1 = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page1->expects(self::never())
            ->method('isVisible');
        $page1->expects(self::never())
            ->method('getResource');
        $page1->expects(self::never())
            ->method('getPrivilege');
        $page1->expects(self::once())
            ->method('getRel')
            ->with($type)
            ->willReturn($page3);
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page3)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame($page3, $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindContainerRelationFromProperty(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $container = new Navigation();

        $page1 = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page1->expects(self::never())
            ->method('isVisible');
        $page1->expects(self::never())
            ->method('getResource');
        $page1->expects(self::never())
            ->method('getPrivilege');
        $page1->expects(self::once())
            ->method('getRel')
            ->with($type)
            ->willReturn($container);
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage)
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame($parentPage, $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindStringRelationFromProperty(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';
        $uri        = 'http://test.org';

        $container = new Navigation();

        $page1 = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page1->expects(self::never())
            ->method('isVisible');
        $page1->expects(self::never())
            ->method('getResource');
        $page1->expects(self::never())
            ->method('getPrivilege');
        $page1->expects(self::once())
            ->method('getRel')
            ->with($type)
            ->willReturn($uri);
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with(new IsInstanceOf(Uri::class))
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertInstanceOf(Uri::class, $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\BadMethodCallException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindConfigRelationFromProperty(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $uri = 'http://test.org';

        $config = new Config(
            ['uri' => $uri]
        );

        $page1 = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page1->expects(self::never())
            ->method('isVisible');
        $page1->expects(self::never())
            ->method('getResource');
        $page1->expects(self::never())
            ->method('getPrivilege');
        $page1->expects(self::once())
            ->method('getRel')
            ->with($type)
            ->willReturn($config);
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with(new IsInstanceOf(Uri::class))
            ->willReturn(true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
            ->method('build')
            ->with(
                AcceptHelperInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($acceptHelper);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(PluginManager::class)
            ->willReturn($helperPluginManager);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertInstanceOf(Uri::class, $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\BadMethodCallException
     * @throws \Laminas\View\Exception\DomainException
     *
     * @return void
     */
    public function testRenderLinkWithError(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $rel = 'test';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(sprintf('Invalid relation attribute "%s", must be "rel" or "rev"', $rel));

        $helper->renderLink($page, $rel, 'test');
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \Laminas\View\Exception\BadMethodCallException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderLinkWithoutHref(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn('');
        $page->expects(self::never())
            ->method('getTarget');

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame('', $helper->renderLink($page, $rel, 'test'));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \Laminas\View\Exception\BadMethodCallException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderLinkWithHref(): void
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
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

        $href  = '/test.html';
        $label = 'test-label';

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
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($label);
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($href);
        $page->expects(self::never())
            ->method('getTarget');

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $attrib   = 'rel';
        $relation = 'test';
        $expected = '<link />';

        $params = (object) [
            $attrib => $relation,
            'href' => $href,
            'title' => $label,
        ];

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::once())
            ->method('itemToString')
            ->with($params)
            ->willReturn($expected);

        \assert($serviceLocator instanceof ContainerInterface);
        \assert($logger instanceof Logger);
        \assert($htmlify instanceof HtmlifyInterface);
        \assert($containerParser instanceof ContainerParserInterface);
        \assert($rootFinder instanceof FindRootInterface);
        \assert($headLink instanceof HeadLink);
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($expected, $helper->renderLink($page, $attrib, $relation));
    }
}
