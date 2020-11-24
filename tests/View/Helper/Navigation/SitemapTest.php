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
use Laminas\Uri\Exception\InvalidUriException;
use Laminas\Uri\Exception\InvalidUriPartException;
use Laminas\Uri\UriInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\SitemapInterface;
use Mezzio\Navigation\Navigation;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Uri;
use PHPUnit\Framework\TestCase;

final class SitemapTest extends TestCase
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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Sitemap::setDefaultRole($defaultRole);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        /* @var AuthorizationInterface $defaultAuth */
        Sitemap::setDefaultAuthorization($defaultAuth);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([null], [$container])
            ->willReturnOnConsecutiveCalls(null, $container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException(new InvalidArgumentException('test'));

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
            ->with(Sitemap::class, $page)
            ->willReturn($expected);

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with(null)
            ->willReturn(null);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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

        $container = new Navigation();
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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
    public function testSetUseXmlDeclaration(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertTrue($helper->getUseXmlDeclaration());

        $helper->setUseXmlDeclaration(false);

        self::assertFalse($helper->getUseXmlDeclaration());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetUseSchemaValidation(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertFalse($helper->getUseSchemaValidation());

        $helper->setUseSchemaValidation(true);

        self::assertTrue($helper->getUseSchemaValidation());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetUseSitemapValidators(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertTrue($helper->getUseSitemapValidators());

        $helper->setUseSitemapValidators(false);

        self::assertFalse($helper->getUseSitemapValidators());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetInvalidServerUrl(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $uri = 'ftp://test.org';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid server URL');

        $helper->setServerUrl($uri);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetInvalidTypeOfServerUrl(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('$serverUrl should be aa string or an Instance of %s', UriInterface::class));

        $helper->setServerUrl([]);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetServerUrlWithInvalidFragment(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $uri = $this->getMockBuilder(UriInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uri->expects(self::once())
            ->method('setFragment')
            ->with('')
            ->willThrowException(new InvalidUriPartException('test'));
        $uri->expects(self::never())
            ->method('toString');
        $uri->expects(self::never())
            ->method('setPath');
        $uri->expects(self::never())
            ->method('setQuery');
        $uri->expects(self::never())
            ->method('isValid');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid server URL');

        /* @var UriInterface $uri */
        $helper->setServerUrl($uri);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetServerUrlWithInvalidUri(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $uri = $this->getMockBuilder(UriInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uri->expects(self::once())
            ->method('setFragment')
            ->with('');
        $uri->expects(self::never())
            ->method('toString');
        $uri->expects(self::once())
            ->method('setPath')
            ->with('');
        $uri->expects(self::once())
            ->method('setQuery')
            ->with('');
        $uri->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid server URL');

        /* @var UriInterface $uri */
        $helper->setServerUrl($uri);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetServerUrlWithError(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $uri = $this->getMockBuilder(UriInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uri->expects(self::once())
            ->method('setFragment')
            ->with('');
        $uri->expects(self::once())
            ->method('toString')
            ->willThrowException(new InvalidUriException('test'));
        $uri->expects(self::once())
            ->method('setPath')
            ->with('');
        $uri->expects(self::once())
            ->method('setQuery')
            ->with('');
        $uri->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid server URL');

        /* @var UriInterface $uri */
        $helper->setServerUrl($uri);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetServerUrl(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $serverUrl = 'ftp://test.org';

        $uri = $this->getMockBuilder(UriInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uri->expects(self::once())
            ->method('setFragment')
            ->with('');
        $uri->expects(self::once())
            ->method('toString')
            ->willReturn($serverUrl);
        $uri->expects(self::once())
            ->method('setPath')
            ->with('');
        $uri->expects(self::once())
            ->method('setQuery')
            ->with('');
        $uri->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        /* @var UriInterface $uri */
        $helper->setServerUrl($uri);

        self::assertSame($serverUrl, $helper->getServerUrl());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetFormatOutput(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertFalse($helper->getFormatOutput());

        $helper->setFormatOutput(true);

        self::assertTrue($helper->getFormatOutput());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testGetServerUrl(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'ftp://test.org';

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame($serverUrl, $helper->getServerUrl());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testUrlWithoutPageHref(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn('');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame('', $helper->url($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testUrlWithRelativePageHref(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'ftp://test.org';
        $uri       = '/';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $uri)
            ->willReturn($serverUrl . '/' . $uri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($uri);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame($serverUrl . '/' . $uri, $helper->url($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testUrlWithAbsolutePageHref(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $uri = 'ftp://test.org';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($uri)
            ->willReturn($uri . '/');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($uri);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame($uri . '/', $helper->url($page));
        self::assertSame('', $helper->url($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testUrlWithRelativePageHref2(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $baseUri = '/test';

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::once())
            ->method('__invoke')
            ->willReturn($baseUri);

        $serverUrl = 'ftp://test.org';
        $uri       = 'test.html';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $baseUri . '/' . $uri)
            ->willReturn($serverUrl . '/' . $baseUri . '/' . $uri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($uri);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame($serverUrl . '/' . $baseUri . '/' . $uri, $helper->url($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetDomDocument(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        $htmlify = $this->getMockBuilder(HtmlifyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::never())
            ->method('parseContainer');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $dom = $this->createMock(\DOMDocument::class);

        self::assertInstanceOf(\DOMDocument::class, $helper->getDom());

        /* @var \DOMDocument $dom */
        $helper->setDom($dom);

        self::assertSame($dom, $helper->getDom());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Validator\Exception\RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testGetDomSitemapOneActivePageRecursive(): void
    {
        $logger = $this->createMock(Logger::class);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        /** @var HtmlifyInterface $htmlify */
        /** @var ContainerParserInterface $containerParser */
        /** @var BasePath $basePath */
        /** @var EscapeHtml $escaper */
        /** @var ServerUrlHelper $serverUrlHelper */
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::once())
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(0);

        $domElement = $this->createMock(\DOMElement::class);

        $dom = $this->getMockBuilder(\DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::once())
            ->method('createElementNS')
            ->with(SitemapInterface::SITEMAP_NS, 'urlset')
            ->willReturn($domElement);
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($domElement);

        /* @var \DOMDocument $dom */
        $helper->setDom($dom);

        self::assertSame($dom, $helper->getDomSitemap());
    }
}
