<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace MezzioTest\Navigation\LaminasView\View\Helper\Navigation;

use Laminas\Log\Logger;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Exception\ExceptionInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\LinksInterface;
use Mezzio\Navigation\Navigation;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Route;
use Mezzio\Navigation\Page\Uri;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\FindFromProperty\FindFromPropertyInterface;
use Mimmi20\NavigationHelper\FindRoot\FindRootInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

use function assert;
use function get_class;
use function sprintf;

final class LinksTest extends TestCase
{
    protected function tearDown(): void
    {
        Links::setDefaultAuthorization(null);
        Links::setDefaultRole(null);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth = 4;

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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetMinDepth(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth(4);

        self::assertSame(4, $helper->getMinDepth());

        $helper->setMinDepth(-1);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth(0);

        self::assertSame(0, $helper->getMinDepth());

        $helper->setMinDepth(1);

        self::assertSame(1, $helper->getMinDepth());

        $helper->setMinDepth(4);

        self::assertSame(4, $helper->getMinDepth());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetRenderInvisible(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetRole(): void
    {
        $role        = 'testRole';
        $defaultRole = 'testDefaultRole';

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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

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
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetUseAuthorization(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());

        $helper->setUseAuthorization();

        self::assertTrue($helper->getUseAuthorization());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetAuthorization(): void
    {
        $auth        = $this->createMock(AuthorizationInterface::class);
        $defaultAuth = $this->createMock(AuthorizationInterface::class);

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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        assert($defaultAuth instanceof AuthorizationInterface);
        Links::setDefaultAuthorization($defaultAuth);

        self::assertSame($defaultAuth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame($auth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetView(): void
    {
        $view = $this->createMock(RendererInterface::class);

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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertNull($helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
        self::assertSame($serviceLocator, $helper->getServiceLocator());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);

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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        $helper->setContainer();

        $container2 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container2);
        self::assertNotSame($container1, $container2);

        $helper->setContainer($container);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
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

        $name = 'default';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testSetContainerWithStringFound(): void
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

        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testDoNotAccept(): void
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

        $container = $this->createMock(ContainerInterface::class);
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setContainer($name);
        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testHtmlify(): void
    {
        $expected = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslatedAndEscaped</a>';

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

        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
        $page->expects(self::never())
            ->method('get');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setContainer($name);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
        self::assertSame($expected, $helper->htmlify($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetIndent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveNoActivePages(): void
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

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findActiveHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePage(): void
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

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 0,
                ]
            );

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findActiveHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveWithoutContainer(): void
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

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with(new IsInstanceOf(Navigation::class), $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findActiveHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageWithoutDepth(): void
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

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 0,
                ]
            );

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findActiveHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $page,
            'depth' => 0,
        ];

        $helper->setMinDepth($minDepth);
        $helper->setMaxDepth($maxDepth);

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageOutOfRange(): void
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

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 2;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findActiveHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageRecursive(): void
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
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::never())
            ->method('isActive');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role     = 'testRole';
        $maxDepth = 0;
        $minDepth = 0;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $parentPage,
                    'depth' => 0,
                ]
            );

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findActiveHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [
            'page' => $parentPage,
            'depth' => 0,
        ];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageRecursive2(): void
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

        $name = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setActive(true);
        $parentPage->setUri('parent');
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page1 = new Uri();
        $page1->setActive(true);
        $page1->setUri('test1');

        $page2 = new Uri();
        $page2->setActive(true);
        $page1->setUri('test2');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setActive(true);
        $parentParentPage->setUri('parentParent');

        $parentParentParentPage = new Uri();
        $parentParentParentPage->setVisible(true);
        $parentParentParentPage->setActive(true);
        $parentParentParentPage->setUri('parentParentParent');

        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $container = new Navigation();
        $container->addPage($parentParentParentPage);

        $role     = 'testRole';
        $maxDepth = 1;
        $minDepth = 2;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findActiveHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageRecursive3(): void
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

        $name = 'Mezzio\\Navigation\\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setActive(true);
        $parentPage->setUri('parent');
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

        $page1 = new Uri();
        $page1->setActive(true);
        $page1->setUri('test1');

        $page2 = new Uri();
        $page2->setActive(true);
        $page1->setUri('test2');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $parentParentPage = new Uri();
        $parentParentPage->setVisible(true);
        $parentParentPage->setActive(true);
        $parentParentPage->setUri('parentParent');

        $parentParentParentPage = new Uri();
        $parentParentParentPage->setVisible(true);
        $parentParentParentPage->setActive(true);
        $parentParentParentPage->setUri('parentParentParent');

        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $container = new Navigation();
        $container->addPage($parentParentParentPage);

        $role     = 'testRole';
        $maxDepth = -1;

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, $maxDepth)
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findActiveHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setMinDepth(-1);
        $helper->setMaxDepth($maxDepth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetRenderFlag(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertSame(LinksInterface::RENDER_ALL, $helper->getRenderFlag());

        $helper->setRenderFlag(LinksInterface::RENDER_ALTERNATE);

        self::assertSame(LinksInterface::RENDER_ALTERNATE, $helper->getRenderFlag());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSearchRevSubsectionWithoutParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn(null);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
        self::assertNull($helper->searchRevSubsection($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRevSubsectionWithParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertNull($helper->searchRevSubsection($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRevSubsectionWithDeepParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertSame($parentPage, $helper->searchRevSubsection($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSearchRevSectionWithoutParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn(null);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
        self::assertNull($helper->searchRevSection($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRevSectionWithParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $parentPage = new Route();

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('hasPage');

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertNull($helper->searchRevSection($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRevSectionWithDeepParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertSame($parentPage, $helper->searchRevSection($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSearchRelSubsectionWithoutParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('hasPages')
            ->willReturn(false);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
        self::assertSame([], $helper->searchRelSubsection($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSubsectionWithParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
        self::assertSame([], $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSubsectionWithDeepParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page], $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSubsectionWithDeepParent2(): void
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

        $page1                  = new Route();
        $page2                  = new Route();
        $parentPage             = new Route();
        $parentParentPage       = new Route();
        $parentParentParentPage = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);
        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$page1], [$page2])
            ->willReturnOnConsecutiveCalls(false, true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(2))
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page2], $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSubsectionWithDeepParent3(): void
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

        $page1                  = new Route();
        $page2                  = new Route();
        $page3                  = new Route();
        $parentPage             = new Route();
        $parentParentPage       = new Route();
        $parentParentParentPage = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);
        $parentPage->addPage($page3);
        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$page1], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls(false, true, true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(3))
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page2, $page3], $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSearchRelSectionWithoutParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('hasPages')
            ->willReturn(false);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
        self::assertSame([], $helper->searchRelSection($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSectionWithParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertSame([], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSectionWithDeepParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSectionWithDeepParent2(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(2))
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSectionWithDeepParent3(): void
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

        $page1            = new Route();
        $page2            = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$page1], [$page2])
            ->willReturnOnConsecutiveCalls(false, true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(2))
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page2], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSectionWithDeepParent4(): void
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

        $page1            = new Route();
        $page2            = new Route();
        $page3            = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);
        $parentPage->addPage($page3);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$page1], [$page2], [$page3])
            ->willReturnOnConsecutiveCalls(false, true, true);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(3))
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page2, $page3], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelSectionWithDeepParent5(): void
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
            ->willReturn(false);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testSearchRelChapterWithoutParent(): void
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

        $page = new Route();
        $role = null;
        $auth = null;

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page, 'rel', 'start')
            ->willReturn([]);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertSame([], $helper->searchRelChapter($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testSearchRelChapterWithParent(): void
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

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentPage, 'rel', 'start')
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls($findFromPropertyHelper, $acceptHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page], $helper->searchRelChapter($parentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testSearchRelChapterWithDeepParent(): void
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

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentParentPage, 'rel', 'start')
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(2))
            ->method('build')
            ->withConsecutive(
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls($findFromPropertyHelper, $acceptHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$parentPage], $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testSearchRelChapterWithDeepParent2(): void
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

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentParentPage, 'rel', 'start')
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(3))
            ->method('build')
            ->withConsecutive(
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls($findFromPropertyHelper, $acceptHelper, $acceptHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$parentPage], $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testSearchRelChapterWithDeepParent3(): void
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
            ->willReturnOnConsecutiveCalls(false, false);

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentParentPage, 'rel', 'start')
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(3))
            ->method('build')
            ->withConsecutive(
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls($findFromPropertyHelper, $acceptHelper, $acceptHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testSearchRelChapterWithDeepParent4(): void
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
            ->willReturnOnConsecutiveCalls(true, true);

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentParentPage, 'rel', 'start')
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(3))
            ->method('build')
            ->withConsecutive(
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls($findFromPropertyHelper, $acceptHelper, $acceptHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$parentPage2, $parentPage], $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelPrevWithoutParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertNull($helper->searchRelPrev($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelPrevWithParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(3))
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertNull($helper->searchRelPrev($page1));
        self::assertSame($page1, $helper->searchRelPrev($page2));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelPrevWithParent2(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(7))
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertNull($helper->searchRelPrev($page1));
        self::assertNull($helper->searchRelPrev($page2));
        self::assertSame($page1, $helper->searchRelPrev($page3));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelNextWithoutParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertNull($helper->searchRelNext($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelNextWithParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertNull($helper->searchRelNext($page2));
        self::assertSame($page2, $helper->searchRelNext($page1));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelStartWithoutParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        self::assertNull($helper->searchRelStart($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelStartWithParent(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(2))
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame($parentPage, $helper->searchRelStart($page1));
        self::assertSame($parentPage, $helper->searchRelStart($page2));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSearchRelStartWithParent2(): void
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

        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('rewind');
        $container->expects(self::exactly(2))
            ->method('current')
            ->willReturn($parentPage);

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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(2))
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
            ->willReturnOnConsecutiveCalls($container, $container);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame($parentPage, $helper->searchRelStart($page1));
        self::assertSame($parentPage, $helper->searchRelStart($page2));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testFindRelationWithError(): void
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

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'test';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(sprintf('Invalid argument: $rel must be "rel" or "rev"; "%s" given', $rel));
        $this->expectExceptionCode(0);

        $helper->findRelation($page1, $rel, 'test');
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testFindNullRelationFromProperty(): void
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
        $page1->expects(self::never())
            ->method('getRel');
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testFindPageRelationFromProperty(): void
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
        $page1->expects(self::never())
            ->method('getRel');
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([$page3]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([$page3], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     *
     * @group Render2
     */
    public function testFindContainerRelationFromProperty(): void
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
        $page1->expects(self::never())
            ->method('getRel');
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([$parentPage]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([$parentPage], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testFindStringRelationFromProperty(): void
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
        $page1->expects(self::never())
            ->method('getRel');
        $page1->expects(self::never())
            ->method('getRev');

        $page3 = $this->createMock(PageInterface::class);

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([$page3]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([$page3], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testFindStringRelationFromPropertyWithError(): void
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
        $page1->expects(self::never())
            ->method('getRel');
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testFindConfigRelationFromProperty(): void
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

        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $page3 = $this->createMock(PageInterface::class);
        $page1 = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page1->expects(self::never())
            ->method('isVisible');
        $page1->expects(self::never())
            ->method('getResource');
        $page1->expects(self::never())
            ->method('getPrivilege');
        $page1->expects(self::never())
            ->method('getRel');
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([$page3]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([$page3], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testFindConfigRelationFromPropertyWithError(): void
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
        $page1->expects(self::never())
            ->method('getRel');
        $page1->expects(self::never())
            ->method('getRev');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([]);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ]
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws DomainException
     */
    public function testRenderLinkWithError(): void
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
        $page->expects(self::never())
            ->method('get');

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'test';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(sprintf('Invalid relation attribute "%s", must be "rel" or "rev"', $rel));
        $this->expectExceptionCode(0);

        $helper->renderLink($page, $rel, 'test');
    }

    /**
     * @throws Exception
     * @throws DomainException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testRenderLinkWithoutHref(): void
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
        $page->expects(self::never())
            ->method('get');

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame('', $helper->renderLink($page, $rel, 'test'));
    }

    /**
     * @throws Exception
     * @throws DomainException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testRenderLinkWithHref(): void
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
        $page->expects(self::exactly(5))
            ->method('get')
            ->withConsecutive(['type'], ['hreflang'], ['charset'], ['lang'], ['media'])
            ->willReturnCallback(static function (string $param): ?string {
                if ('type' === $param || 'media' === $param) {
                    return null;
                }

                if ('hreflang' === $param || 'lang' === $param) {
                    return 'de';
                }

                throw new \Mezzio\Navigation\Exception\InvalidArgumentException('fail');
            });

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            'hreflang' => 'de',
            'lang' => 'de',
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

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame($expected, $helper->renderLink($page, $attrib, $relation));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testDoNotRenderIfNoPageIsActive(): void
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

        $maxDepth = null;
        $minDepth = 0;

        $container = $this->createMock(ContainerInterface::class);

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::once())
            ->method('build')
            ->with(
                FindActiveInterface::class,
                [
                    'authorization' => null,
                    'renderInvisible' => false,
                    'role' => null,
                ]
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(3))
            ->method('parseContainer')
            ->withConsecutive([$container], [null], [$container])
            ->willReturnOnConsecutiveCalls($container, null, $container);

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
        $headLink->expects(self::never())
            ->method('itemToString');

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setContainer($container);

        self::assertSame('', $helper->render());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testRender(): void
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

        $name      = 'Mezzio\\Navigation\\Top';
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentLabel = 'parent-label';
        $parentUri   = '##';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri($parentUri);
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::exactly(2))
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ]
            );

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::exactly(31))
            ->method('find')
            ->withConsecutive(
                [$page, 'rel', 'alternate'],
                [$page, 'rel', 'stylesheet'],
                [$page, 'rel', 'start'],
                [$page, 'rel', 'next'],
                [$page, 'rel', 'prev'],
                [$page, 'rel', 'contents'],
                [$page, 'rel', 'index'],
                [$page, 'rel', 'glossary'],
                [$page, 'rel', 'copyright'],
                [$page, 'rel', 'chapter'],
                [$page, 'rel', 'start'],
                [$page, 'rel', 'section'],
                [$page, 'rel', 'subsection'],
                [$page, 'rel', 'appendix'],
                [$page, 'rel', 'help'],
                [$page, 'rel', 'bookmark'],
                [$page, 'rev', 'alternate'],
                [$page, 'rev', 'stylesheet'],
                [$page, 'rev', 'start'],
                [$page, 'rev', 'next'],
                [$page, 'rev', 'prev'],
                [$page, 'rev', 'contents'],
                [$page, 'rev', 'index'],
                [$page, 'rev', 'glossary'],
                [$page, 'rev', 'copyright'],
                [$page, 'rev', 'chapter'],
                [$page, 'rev', 'section'],
                [$page, 'rev', 'subsection'],
                [$page, 'rev', 'appendix'],
                [$page, 'rev', 'help'],
                [$page, 'rev', 'bookmark']
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                []
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page], [$parentPage])
            ->willReturnOnConsecutiveCalls(true, true, true);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(35))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $acceptHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $acceptHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $acceptHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper
            );

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
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::exactly(2))
            ->method('setRoot')
            ->withConsecutive([$container], [null]);
        $rootFinder->expects(self::exactly(7))
            ->method('find')
            ->with($page)
            ->willReturn($parentPage);

        $expected = sprintf('<link rel="start" href="%s" title="%s" />', $parentUri, $parentLabel);

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::once())
            ->method('itemToString')
            ->with((object) ['rel' => 'start', 'href' => $parentUri, 'title' => $parentLabel])
            ->willReturn($expected);

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->render($name));
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\DomainException
     */
    public function testRender2(): void
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

        $name      = 'Mezzio\\Navigation\\Top';
        $resource  = 'testResource';
        $privilege = 'testPrivilege';
        $uri       = '';

        $parentLabel = 'parent-label';
        $parentUri   = '##';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri($parentUri);
        $parentPage->setTarget('self');
        $parentPage->setLabel($parentLabel);
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

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
            ->method('getLabel');
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::never())
            ->method('getTitle');
        $page->expects(self::never())
            ->method('getId');
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($uri);
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('get');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        $findActiveHelper = $this->getMockBuilder(FindActiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $parentPage,
                    'depth' => 1,
                ]
            );

        $findFromPropertyHelper = $this->getMockBuilder(FindFromPropertyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $findFromPropertyHelper->expects(self::exactly(31))
            ->method('find')
            ->withConsecutive(
                [$parentPage, 'rel', 'alternate'],
                [$parentPage, 'rel', 'stylesheet'],
                [$parentPage, 'rel', 'start'],
                [$parentPage, 'rel', 'next'],
                [$parentPage, 'rel', 'prev'],
                [$parentPage, 'rel', 'contents'],
                [$parentPage, 'rel', 'index'],
                [$parentPage, 'rel', 'glossary'],
                [$parentPage, 'rel', 'copyright'],
                [$parentPage, 'rel', 'chapter'],
                [$parentPage, 'rel', 'start'],
                [$parentPage, 'rel', 'section'],
                [$parentPage, 'rel', 'subsection'],
                [$parentPage, 'rel', 'appendix'],
                [$parentPage, 'rel', 'help'],
                [$parentPage, 'rel', 'bookmark'],
                [$parentPage, 'rev', 'alternate'],
                [$parentPage, 'rev', 'stylesheet'],
                [$parentPage, 'rev', 'start'],
                [$parentPage, 'rev', 'next'],
                [$parentPage, 'rev', 'prev'],
                [$parentPage, 'rev', 'contents'],
                [$parentPage, 'rev', 'index'],
                [$parentPage, 'rev', 'glossary'],
                [$parentPage, 'rev', 'copyright'],
                [$parentPage, 'rev', 'chapter'],
                [$parentPage, 'rev', 'section'],
                [$parentPage, 'rev', 'subsection'],
                [$parentPage, 'rev', 'appendix'],
                [$parentPage, 'rev', 'help'],
                [$parentPage, 'rev', 'bookmark']
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                []
            );

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->withConsecutive([$page], [$page])
            ->willReturnOnConsecutiveCalls(true, true);

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::exactly(34))
            ->method('build')
            ->withConsecutive(
                [
                    FindActiveInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    AcceptHelperInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ],
                [
                    FindFromPropertyInterface::class,
                    [
                        'authorization' => $auth,
                        'renderInvisible' => false,
                        'role' => $role,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $findActiveHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $acceptHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $acceptHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper,
                $findFromPropertyHelper
            );

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
            ->withConsecutive([$name], [$container])
            ->willReturnOnConsecutiveCalls($container, $container);

        $rootFinder = $this->getMockBuilder(FindRootInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootFinder->expects(self::exactly(2))
            ->method('setRoot')
            ->withConsecutive([$container], [null]);
        $rootFinder->expects(self::exactly(7))
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentPage);

        $expected = '';

        $headLink = $this->getMockBuilder(HeadLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::never())
            ->method('itemToString');

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->render($name));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testToStringExceptionInRenderer(): void
    {
        $auth      = $this->createMock(AuthorizationInterface::class);
        $exception = new InvalidArgumentException('test');

        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::once())
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
            ->willThrowException($exception);

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
        $headLink->expects(self::never())
            ->method('itemToString');

        $helper = new Links(
            $serviceLocator,
            $logger,
            $htmlify,
            $containerParser,
            $rootFinder,
            $headLink
        );

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame('', (string) $helper);
    }
}
