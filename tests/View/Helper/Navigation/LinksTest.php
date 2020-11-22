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
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\FindRootInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\LinksInterface;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Route;
use Mezzio\Navigation\Page\Uri;
use PHPUnit\Framework\TestCase;

final class LinksTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMinDepth(): void
    {
        $minDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth($minDepth);

        self::assertSame($minDepth, $helper->getMinDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRenderInvisible(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRole(): void
    {
        $role           = 'testRole';
        $defaultRole    = 'testDefaultRole';
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     *
     * @return void
     */
    public function testSetUseAuthorization(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetAuthorization(): void
    {
        $auth           = $this->createMock(AuthorizationInterface::class);
        $defaultAuth    = $this->createMock(AuthorizationInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     *
     * @return void
     */
    public function testSetView(): void
    {
        $view           = $this->createMock(RendererInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     *
     * @return void
     */
    public function testSetContainer(): void
    {
        $container      = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(\Mezzio\Navigation\Navigation::class, $container1);

        /* @var AuthorizationInterface $auth */
        $helper->setContainer();

        $container2 = $helper->getContainer();

        self::assertInstanceOf(\Mezzio\Navigation\Navigation::class, $container2);
        self::assertNotSame($container1, $container2);

        $helper->setContainer($container);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'default';

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     *
     * @return void
     */
    public function testSetContainerWithStringFound(): void
    {
        $logger    = $this->createMock(Logger::class);
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testDoNotAcceptInvisiblePages(): void
    {
        $logger    = $this->createMock(Logger::class);
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        $helper->setContainer($name);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

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
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testDoNotAcceptByAuthorization(): void
    {
        $logger    = $this->createMock(Logger::class);
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        $helper->setContainer($name);

        $role = 'testRole';

        $helper->setRole($role);

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(false);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

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
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testDoNotAcceptByAuthorizationWithParent(): void
    {
        $logger    = $this->createMock(Logger::class);
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        $helper->setContainer($name);

        $role = 'testRole';

        $helper->setRole($role);

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

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

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlify(): void
    {
        $expected  = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslatedAndEscaped</a>';
        $logger    = $this->createMock(Logger::class);
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     *
     * @return void
     */
    public function testSetIndent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveNoActivePages(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $parentPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $parentPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $parentPage->expects(self::once())
            ->method('getParent')
            ->willReturn(null);
        $parentPage->expects(self::never())
            ->method('isActive')
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
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(false);

        $container = new \Mezzio\Navigation\Navigation();
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, 0, 42));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveOneActivePage(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $parentPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $parentPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $parentPage->expects(self::once())
            ->method('getParent')
            ->willReturn(null);
        $parentPage->expects(self::never())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

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
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        $container = new \Mezzio\Navigation\Navigation();
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

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
     *
     * @return void
     */
    public function testFindActiveWithoutContainer(): void
    {
        $logger = $this->createMock(Logger::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveOneActivePageWithoutDepth(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $parentPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $parentPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $parentPage->expects(self::once())
            ->method('getParent')
            ->willReturn(null);
        $parentPage->expects(self::never())
            ->method('isActive');

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
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        $container = new \Mezzio\Navigation\Navigation();
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

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
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveOneActivePageOutOfRange(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

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

        $container = new \Mezzio\Navigation\Navigation();
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testFindActiveOneActivePageRecursive(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

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
        $page->expects(self::exactly(2))
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        $parentPage->addPage($page);

        $container = new \Mezzio\Navigation\Navigation();
        $container->addPage($parentPage);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::exactly(3))
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

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
     *
     * @return void
     */
    public function testSetRenderFlag(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame(LinksInterface::RENDER_ALL, $helper->getRenderFlag());

        $helper->setRenderFlag(LinksInterface::RENDER_ALTERNATE);

        self::assertSame(LinksInterface::RENDER_ALTERNATE, $helper->getRenderFlag());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRevSubsectionWithoutParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     *
     * @return void
     */
    public function testSearchRevSubsectionWithParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRevSubsection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRevSubsectionWithDeepParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame($parentPage, $helper->searchRevSubsection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRevSectionWithoutParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     *
     * @return void
     */
    public function testSearchRevSectionWithParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRevSection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRevSectionWithDeepParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame($parentPage, $helper->searchRevSection($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelSubsectionWithoutParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     *
     * @return void
     */
    public function testSearchRelSubsectionWithParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        /* @var PageInterface $page */
        self::assertNull($helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelSubsectionWithDeepParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $page                   = new Route();
        $parentPage             = new Route();
        $parentParentPage       = new Route();
        $parentParentParentPage = new Route();

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
            ->with($parentPage)
            ->willReturn($parentParentParentPage);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame($page, $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelSectionWithoutParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

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
     *
     * @return void
     */
    public function testSearchRelSectionWithParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRelSection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelSectionWithDeepParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $page             = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

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
            ->with($parentPage)
            ->willReturn($parentParentPage);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame($page, $helper->searchRelSection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelSectionWithDeepParent2(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $page  = new Route();
        $page2 = new Route();
        $page2->setActive(false);
        $page2->setVisible(false);
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page2);
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
            ->with($parentPage)
            ->willReturn($parentParentPage);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame($page, $helper->searchRelSection($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelChapterWithoutParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRelChapter($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelChapterWithParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$parentPage], [$parentPage])
            ->willReturnOnConsecutiveCalls($parentPage, $parentPage);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame($page, $helper->searchRelChapter($parentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelChapterWithDeepParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $page             = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

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
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$parentParentPage], [$parentParentPage])
            ->willReturnOnConsecutiveCalls($parentParentPage, $parentParentPage);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame($parentPage, $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelChapterWithDeepParent2(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $page        = new Route();
        $parentPage  = new Route();
        $parentPage2 = new Route();
        $parentPage2->setActive(false);
        $parentPage2->setVisible(false);
        $parentParentPage = new Route();

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage2);
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
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([$parentParentPage], [$parentParentPage])
            ->willReturnOnConsecutiveCalls($parentParentPage, $parentParentPage);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame($parentPage, $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelPrevWithoutParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRelPrev($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelPrevWithParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRelPrev($page1));
        self::assertSame($page1, $helper->searchRelPrev($page2));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelPrevWithParent2(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();
        $page2->setActive(false);
        $page2->setVisible(false);
        $page3 = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);
        $parentPage->addPage($page3);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRelPrev($page1));
        self::assertNull($helper->searchRelPrev($page2));
        self::assertSame($page1, $helper->searchRelPrev($page3));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelNextWithoutParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRelNext($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelNextWithParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRelNext($page2));
        self::assertSame($page2, $helper->searchRelNext($page1));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelStartWithoutParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertNull($helper->searchRelStart($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSearchRelStartWithParent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var FindRootInterface $rootFinder */
        $helper = new Links($serviceLocator, $logger, $htmlify, $containerParser, $rootFinder);

        self::assertSame($parentPage, $helper->searchRelStart($page1));
        self::assertSame($parentPage, $helper->searchRelStart($page2));
    }
}
