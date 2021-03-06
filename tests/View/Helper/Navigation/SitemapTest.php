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

use DOMDocument;
use DOMElement;
use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\Uri\Exception\InvalidUriException;
use Laminas\Uri\Exception\InvalidUriPartException;
use Laminas\Uri\UriInterface;
use Laminas\Validator\Sitemap\Changefreq;
use Laminas\Validator\Sitemap\Lastmod;
use Laminas\Validator\Sitemap\Loc;
use Laminas\Validator\Sitemap\Priority;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\Navigation\Helper\AcceptHelperInterface;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\FindActiveInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Helper\PluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\SitemapInterface;
use Mezzio\Navigation\Navigation;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Uri;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

use function assert;
use function date;
use function get_class;
use function sprintf;
use function time;

final class SitemapTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function tearDown(): void
    {
        Sitemap::setDefaultAuthorization(null);
        Sitemap::setDefaultRole(null);
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        assert($defaultAuth instanceof AuthorizationInterface);
        Sitemap::setDefaultAuthorization($defaultAuth);

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertNull($helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
        self::assertSame($serviceLocator, $helper->getServiceLocator());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);

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
            ->withConsecutive([null], [$container])
            ->willReturnOnConsecutiveCalls(null, $container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
     * @throws ExceptionInterface
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
     * @throws ExceptionInterface
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
     * @throws ExceptionInterface
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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, 2, 42));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
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

        $helperPluginManager = $this->getMockBuilder(PluginManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperPluginManager->expects(self::once())
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

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
    public function testSetUseXmlDeclaration(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertTrue($helper->getUseXmlDeclaration());

        $helper->setUseXmlDeclaration(false);

        self::assertFalse($helper->getUseXmlDeclaration());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetUseSchemaValidation(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertFalse($helper->getUseSchemaValidation());

        $helper->setUseSchemaValidation(true);

        self::assertTrue($helper->getUseSchemaValidation());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetUseSitemapValidators(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertTrue($helper->getUseSitemapValidators());

        $helper->setUseSitemapValidators(false);

        self::assertFalse($helper->getUseSitemapValidators());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testSetInvalidServerUrl(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $uri = 'ftp://test.org';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid server URL');
        $this->expectExceptionCode(0);

        $helper->setServerUrl($uri);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testSetInvalidTypeOfServerUrl(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('$serverUrl should be aa string or an Instance of %s', UriInterface::class));
        $this->expectExceptionCode(0);

        $helper->setServerUrl([]);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testSetServerUrlWithInvalidFragment(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
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
        $this->expectExceptionCode(0);

        assert($uri instanceof UriInterface);
        $helper->setServerUrl($uri);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testSetServerUrlWithInvalidUri(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
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
        $this->expectExceptionCode(0);

        assert($uri instanceof UriInterface);
        $helper->setServerUrl($uri);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testSetServerUrlWithError(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
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
        $this->expectExceptionCode(0);

        assert($uri instanceof UriInterface);
        $helper->setServerUrl($uri);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSetServerUrl(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
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

        assert($uri instanceof UriInterface);
        $helper->setServerUrl($uri);

        self::assertSame($serverUrl, $helper->getServerUrl());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetFormatOutput(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertFalse($helper->getFormatOutput());

        $helper->setFormatOutput(true);

        self::assertTrue($helper->getFormatOutput());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetServerUrl(): void
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame($serverUrl, $helper->getServerUrl());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testUrlWithoutPageHref(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn('');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame('', $helper->url($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testUrlWithRelativePageHref(): void
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame($serverUrl . '/' . $uri, $helper->url($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testUrlWithAbsolutePageHref(): void
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame($uri . '/', $helper->url($page));
        self::assertSame('', $helper->url($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testUrlWithRelativePageHref2(): void
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

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        self::assertSame($serverUrl . '/' . $baseUri . '/' . $uri, $helper->url($page));
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetDomDocument(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $dom = $this->createMock(DOMDocument::class);

        self::assertInstanceOf(DOMDocument::class, $helper->getDom());

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        self::assertSame($dom, $helper->getDom());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetLocValidator(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $locValidator = $this->createMock(Loc::class);

        self::assertInstanceOf(Loc::class, $helper->getLocValidator());

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        self::assertSame($locValidator, $helper->getLocValidator());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetLastmodValidator(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $lastmodValidator = $this->createMock(Lastmod::class);

        self::assertInstanceOf(Lastmod::class, $helper->getLastmodValidator());

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        self::assertSame($lastmodValidator, $helper->getLastmodValidator());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetPriorityValidator(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $priorityValidator = $this->createMock(Priority::class);

        self::assertInstanceOf(Priority::class, $helper->getPriorityValidator());

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($priorityValidator, $helper->getPriorityValidator());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetChangefreqValidator(): void
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $changefreqValidator = $this->createMock(Changefreq::class);

        self::assertInstanceOf(Changefreq::class, $helper->getChangefreqValidator());

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        self::assertSame($changefreqValidator, $helper->getChangefreqValidator());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testGetDomSitemapOneActivePageRecursive(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);

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
            ->method('getHref');

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
        $parentPage->addPage($page);

        $container = new Navigation();
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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . '/' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(0);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(3))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . '/' . $parentUri]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testGetDomSitemapOneActivePageRecursiveWithSchemaValidation(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);

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
            ->method('getHref');

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page)
            )
        );
        $parentPage->addPage($page);

        $container = new Navigation();
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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . '/' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(0);
        $helper->setUseSchemaValidation(true);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(3))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . '/' . $parentUri]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::once())
            ->method('schemaValidate')
            ->with(SitemapInterface::SITEMAP_XSD)
            ->willReturn(true);

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testGetDomSitemapOneActivePageRecursiveDeep(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page1], [$page2])
            ->willReturnOnConsecutiveCalls(true, false, true);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . '/' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(3))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . '/' . $parentUri]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLocValidation(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::never())
            ->method('appendChild');

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(2))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url']
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(false);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Encountered an invalid URL for Sitemap XML: "%s"', $serverUrl . 'test' . $parentUri));
        $this->expectExceptionCode(0);

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLocValidationException(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::never())
            ->method('appendChild');

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(2))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url']
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willThrowException(new \Laminas\Validator\Exception\RuntimeException('test'));

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('An error occured while validating an URL for Sitemap XML: "%s"', $serverUrl . 'test' . $parentUri));
        $this->expectExceptionCode(0);

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLastmod(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $time       = time();
        $changefreq = 'never';
        $priority   = '0.9';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);
        $parentPage->set('lastmod', date('Y-m-d H:i:s', $time));
        $parentPage->set('changefreq', $changefreq);
        $parentPage->set('priority', $priority);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page1], [$page2])
            ->willReturnOnConsecutiveCalls(true, false, true);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc        = $this->createMock(DOMElement::class);
        $urlLastMod    = $this->createMock(DOMElement::class);
        $urlChangefreq = $this->createMock(DOMElement::class);
        $urlPriority   = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::exactly(4))
            ->method('appendChild')
            ->withConsecutive([$urlLoc], [$urlLastMod], [$urlChangefreq], [$urlPriority]);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(6))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . 'test' . $parentUri],
                [SitemapInterface::SITEMAP_NS, 'lastmod', date('c', $time)],
                [SitemapInterface::SITEMAP_NS, 'changefreq', $changefreq],
                [SitemapInterface::SITEMAP_NS, 'priority', $priority]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc,
                $urlLastMod,
                $urlChangefreq,
                $urlPriority
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(true);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->getMockBuilder(Lastmod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lastmodValidator->expects(self::once())
            ->method('isValid')
            ->with(date('c', $time))
            ->willReturn(true);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->getMockBuilder(Changefreq::class)
            ->disableOriginalConstructor()
            ->getMock();
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willReturn(true);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priorityValidator->expects(self::once())
            ->method('isValid')
            ->with($priority)
            ->willReturn(true);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithInvalidLastmod(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $time       = time();
        $changefreq = 'never';
        $priority   = '0.9';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);
        $parentPage->set('lastmod', date('Y-m-d H:i:s', $time));
        $parentPage->set('changefreq', $changefreq);
        $parentPage->set('priority', $priority);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page1], [$page2])
            ->willReturnOnConsecutiveCalls(true, false, true);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc        = $this->createMock(DOMElement::class);
        $urlChangefreq = $this->createMock(DOMElement::class);
        $urlPriority   = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::exactly(3))
            ->method('appendChild')
            ->withConsecutive([$urlLoc], [$urlChangefreq], [$urlPriority]);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(5))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . 'test' . $parentUri],
                [SitemapInterface::SITEMAP_NS, 'changefreq', $changefreq],
                [SitemapInterface::SITEMAP_NS, 'priority', $priority]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc,
                $urlChangefreq,
                $urlPriority
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(true);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->getMockBuilder(Lastmod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lastmodValidator->expects(self::once())
            ->method('isValid')
            ->with(date('c', $time))
            ->willReturn(false);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->getMockBuilder(Changefreq::class)
            ->disableOriginalConstructor()
            ->getMock();
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willReturn(true);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priorityValidator->expects(self::once())
            ->method('isValid')
            ->with($priority)
            ->willReturn(true);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLastmodException(): void
    {
        $exception = new \Laminas\Validator\Exception\RuntimeException('test');

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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $time       = time();
        $changefreq = 'never';
        $priority   = '0.9';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);
        $parentPage->set('lastmod', date('Y-m-d H:i:s', $time));
        $parentPage->set('changefreq', $changefreq);
        $parentPage->set('priority', $priority);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page1], [$page2])
            ->willReturnOnConsecutiveCalls(true, false, true);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc        = $this->createMock(DOMElement::class);
        $urlChangefreq = $this->createMock(DOMElement::class);
        $urlPriority   = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::exactly(3))
            ->method('appendChild')
            ->withConsecutive([$urlLoc], [$urlChangefreq], [$urlPriority]);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(5))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . 'test' . $parentUri],
                [SitemapInterface::SITEMAP_NS, 'changefreq', $changefreq],
                [SitemapInterface::SITEMAP_NS, 'priority', $priority]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc,
                $urlChangefreq,
                $urlPriority
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(true);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->getMockBuilder(Lastmod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lastmodValidator->expects(self::once())
            ->method('isValid')
            ->with(date('c', $time))
            ->willThrowException($exception);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->getMockBuilder(Changefreq::class)
            ->disableOriginalConstructor()
            ->getMock();
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willReturn(true);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priorityValidator->expects(self::once())
            ->method('isValid')
            ->with($priority)
            ->willReturn(true);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithInvalidLastmodAndChangeFreq(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $time       = time();
        $changefreq = 'never';
        $priority   = '0.9';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);
        $parentPage->set('lastmod', date('Y-m-d H:i:s', $time));
        $parentPage->set('changefreq', $changefreq);
        $parentPage->set('priority', $priority);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page1], [$page2])
            ->willReturnOnConsecutiveCalls(true, false, true);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(3))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . 'test' . $parentUri]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(true);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->getMockBuilder(Lastmod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lastmodValidator->expects(self::once())
            ->method('isValid')
            ->with(date('c', $time))
            ->willReturn(false);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->getMockBuilder(Changefreq::class)
            ->disableOriginalConstructor()
            ->getMock();
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willReturn(false);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priorityValidator->expects(self::once())
            ->method('isValid')
            ->with($priority)
            ->willReturn(false);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLastmodExceptionAndChangeFreqException(): void
    {
        $exception1 = new \Laminas\Validator\Exception\RuntimeException('test');
        $exception2 = new \Laminas\Validator\Exception\RuntimeException('test');
        $exception3 = new \Laminas\Validator\Exception\RuntimeException('test');

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
            ->withConsecutive([$exception1], [$exception2], [$exception3]);
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $time       = time();
        $changefreq = 'never';
        $priority   = '0.9';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);
        $parentPage->set('lastmod', date('Y-m-d H:i:s', $time));
        $parentPage->set('changefreq', $changefreq);
        $parentPage->set('priority', $priority);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page1], [$page2])
            ->willReturnOnConsecutiveCalls(true, false, true);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc        = $this->createMock(DOMElement::class);
        $urlChangefreq = $this->createMock(DOMElement::class);
        $urlPriority   = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(3))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . 'test' . $parentUri]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(true);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->getMockBuilder(Lastmod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lastmodValidator->expects(self::once())
            ->method('isValid')
            ->with(date('c', $time))
            ->willThrowException($exception1);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->getMockBuilder(Changefreq::class)
            ->disableOriginalConstructor()
            ->getMock();
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willThrowException($exception2);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priorityValidator->expects(self::once())
            ->method('isValid')
            ->with($priority)
            ->willThrowException($exception3);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithoutPriority(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $time       = time();
        $changefreq = 'never';
        $priority   = 0.9;

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);
        $parentPage->set('lastmod', date('Y-m-d H:i:s', $time));
        $parentPage->set('changefreq', $changefreq);
        $parentPage->set('priority', $priority);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page1], [$page2])
            ->willReturnOnConsecutiveCalls(true, false, true);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc        = $this->createMock(DOMElement::class);
        $urlLastMod    = $this->createMock(DOMElement::class);
        $urlChangefreq = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::exactly(3))
            ->method('appendChild')
            ->withConsecutive([$urlLoc], [$urlLastMod], [$urlChangefreq]);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(5))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . 'test' . $parentUri],
                [SitemapInterface::SITEMAP_NS, 'lastmod', date('c', $time)],
                [SitemapInterface::SITEMAP_NS, 'changefreq', $changefreq]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc,
                $urlLastMod,
                $urlChangefreq
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(true);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->getMockBuilder(Lastmod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lastmodValidator->expects(self::once())
            ->method('isValid')
            ->with(date('c', $time))
            ->willReturn(true);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->getMockBuilder(Changefreq::class)
            ->disableOriginalConstructor()
            ->getMock();
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willReturn(true);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priorityValidator->expects(self::once())
            ->method('isValid')
            ->with($priority)
            ->willReturn(false);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testRenderWithXmlDeclaration(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $time       = time();
        $changefreq = 'never';
        $priority   = '0.9';
        $xml        = '<xml />';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);
        $parentPage->set('lastmod', date('Y-m-d H:i:s', $time));
        $parentPage->set('changefreq', $changefreq);
        $parentPage->set('priority', $priority);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page1], [$page2])
            ->willReturnOnConsecutiveCalls(true, false, true);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc        = $this->createMock(DOMElement::class);
        $urlLastMod    = $this->createMock(DOMElement::class);
        $urlChangefreq = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::exactly(3))
            ->method('appendChild')
            ->withConsecutive([$urlLoc], [$urlLastMod], [$urlChangefreq]);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(5))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . 'test' . $parentUri],
                [SitemapInterface::SITEMAP_NS, 'lastmod', date('c', $time)],
                [SitemapInterface::SITEMAP_NS, 'changefreq', $changefreq]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc,
                $urlLastMod,
                $urlChangefreq
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');
        $dom->expects(self::once())
            ->method('saveXML')
            ->with(null)
            ->willReturn($xml);

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(true);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->getMockBuilder(Lastmod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lastmodValidator->expects(self::once())
            ->method('isValid')
            ->with(date('c', $time))
            ->willReturn(true);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->getMockBuilder(Changefreq::class)
            ->disableOriginalConstructor()
            ->getMock();
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willReturn(true);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priorityValidator->expects(self::once())
            ->method('isValid')
            ->with($priority)
            ->willReturn(false);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($xml, $helper->render());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testToStringWithXmlDeclaration(): void
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

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $time       = time();
        $changefreq = 'never';
        $priority   = '0.9';
        $xml        = '<xml />';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);
        $parentPage->set('lastmod', date('Y-m-d H:i:s', $time));
        $parentPage->set('changefreq', $changefreq);
        $parentPage->set('priority', $priority);

        $container = new Navigation();

        $page1 = new Uri();
        $page1->setVisible(false);
        $page1->setOrder(1);

        $page2 = new Uri();
        $page2->setVisible(true);
        $page2->setUri($parentUri);
        $page2->setOrder(2);

        assert(
            $page1 instanceof PageInterface,
            sprintf(
                '$page1 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page1)
            )
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                get_class($page2)
            )
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->getMockBuilder(AcceptHelperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $acceptHelper->expects(self::exactly(3))
            ->method('accept')
            ->withConsecutive([$parentPage], [$page1], [$page2])
            ->willReturnOnConsecutiveCalls(true, false, true);

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

        $basePath = $this->getMockBuilder(BasePath::class)
            ->disableOriginalConstructor()
            ->getMock();
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . 'test' . $parentUri);

        $serverUrlHelper = $this->getMockBuilder(ServerUrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->getMockBuilder(ContainerParserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerParser->expects(self::exactly(2))
            ->method('parseContainer')
            ->withConsecutive([$container], [null])
            ->willReturnOnConsecutiveCalls($container, null);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc        = $this->createMock(DOMElement::class);
        $urlLastMod    = $this->createMock(DOMElement::class);
        $urlChangefreq = $this->createMock(DOMElement::class);

        $urlNode = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlNode->expects(self::exactly(3))
            ->method('appendChild')
            ->withConsecutive([$urlLoc], [$urlLastMod], [$urlChangefreq]);

        $urlSet = $this->getMockBuilder(DOMElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom = $this->getMockBuilder(DOMDocument::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dom->expects(self::exactly(5))
            ->method('createElementNS')
            ->withConsecutive(
                [SitemapInterface::SITEMAP_NS, 'urlset'],
                [SitemapInterface::SITEMAP_NS, 'url'],
                [SitemapInterface::SITEMAP_NS, 'loc', $serverUrl . 'test' . $parentUri],
                [SitemapInterface::SITEMAP_NS, 'lastmod', date('c', $time)],
                [SitemapInterface::SITEMAP_NS, 'changefreq', $changefreq]
            )
            ->willReturnOnConsecutiveCalls(
                $urlSet,
                $urlNode,
                $urlLoc,
                $urlLastMod,
                $urlChangefreq
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');
        $dom->expects(self::once())
            ->method('saveXML')
            ->with(null)
            ->willReturn($xml);

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->getMockBuilder(Loc::class)
            ->disableOriginalConstructor()
            ->getMock();
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(true);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->getMockBuilder(Lastmod::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lastmodValidator->expects(self::once())
            ->method('isValid')
            ->with(date('c', $time))
            ->willReturn(true);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->getMockBuilder(Changefreq::class)
            ->disableOriginalConstructor()
            ->getMock();
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willReturn(true);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priorityValidator->expects(self::once())
            ->method('isValid')
            ->with($priority)
            ->willReturn(false);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($xml, (string) $helper);
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);

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
            ->with($container)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        assert($logger instanceof Logger);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($basePath instanceof BasePath);
        assert($escaper instanceof EscapeHtml);
        assert($serverUrlHelper instanceof ServerUrlHelper);
        $helper = new Sitemap($serviceLocator, $logger, $htmlify, $containerParser, $basePath, $escaper, $serverUrlHelper);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        $helper($container);

        self::assertSame($container, $helper->getContainer());
    }
}
