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

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use DOMDocument;
use DOMElement;
use DOMNode;
use Laminas\Validator\Sitemap\Changefreq;
use Laminas\Validator\Sitemap\Lastmod;
use Laminas\Validator\Sitemap\Loc;
use Laminas\Validator\Sitemap\Priority;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\LaminasView\ServerUrlHelper;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\SitemapInterface;
use Mimmi20\Mezzio\Navigation\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\Mezzio\Navigation\Page\Uri;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

use function assert;
use function date;
use function sprintf;
use function time;

final class SitemapTest extends TestCase
{
    /** @throws void */
    #[Override]
    protected function tearDown(): void
    {
        Sitemap::setDefaultAuthorization();
        Sitemap::setDefaultRole();
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth = 4;

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetMinDepth(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetRenderInvisible(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetRole(): void
    {
        $role        = 'testRole';
        $defaultRole = 'testDefaultRole';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        self::assertSame([], $helper->getRoles());
        self::assertFalse($helper->hasRoles());

        Sitemap::setDefaultRole($defaultRole);

        self::assertSame([$defaultRole], $helper->getRoles());
        self::assertTrue($helper->hasRoles());

        $helper->setRoles([$role]);

        self::assertSame([$role], $helper->getRoles());
        self::assertTrue($helper->hasRoles());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetUseAuthorization(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        self::assertFalse($helper->getUseAuthorization());

        $helper->setUseAuthorization();

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetAuthorization(): void
    {
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $defaultAuth = $this->createMock(AuthorizationInterface::class);
        $defaultAuth->expects(self::never())
            ->method('isGranted');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetView(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        self::assertNull($helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | null $containerParam) use ($matcher, $container): ContainerInterface | null {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertNull($containerParam, (string) $invocation),
                        default => self::assertSame($container, $containerParam, (string) $invocation),
                    };

                    return match ($invocation) {
                        1 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
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
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $name = 'default';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException(new InvalidArgumentException('test'));

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetContainerWithStringFound(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\Navigation\Top';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testDoNotAccept(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\Navigation\Top';

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(false);
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
        $page->expects(self::never())
            ->method('getHref');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getLiClass');

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $role = 'testRole';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        $helper->setContainer($name);
        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testHtmlify(): void
    {
        $expected = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslatedAndEscaped</a>';

        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\Navigation\Top';

        $page = $this->createMock(PageInterface::class);
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

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Sitemap::class, $page)
            ->willReturn($expected);

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        $helper->setContainer($name);

        $view = $this->createMock(PhpRenderer::class);
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
                $page::class,
            ),
        );
        self::assertSame($expected, $helper->htmlify($page));
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetIndent(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testToStringWithXmlDeclaration(): void
    {
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
                $page1::class,
            ),
        );
        $parentPage->addPage($page1);

        assert(
            $page2 instanceof PageInterface,
            sprintf(
                '$page2 should be an Instance of %s, but was %s',
                PageInterface::class,
                $page2::class,
            ),
        );
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege, null)
            ->willReturn(true);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org:8081';

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . '-test-' . $parentUri);

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturn($serverUrl);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $container): ContainerInterface | null {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        2 => self::assertNull($containerParam, (string) $invocation),
                        default => self::assertSame($container, $containerParam, (string) $invocation),
                    };

                    return match ($invocation) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);
        $helper->setUseAuthorization();

        $urlLoc        = $this->createMock(DOMElement::class);
        $urlLastMod    = $this->createMock(DOMElement::class);
        $urlChangefreq = $this->createMock(DOMElement::class);

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(3);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlLastMod, $urlChangefreq): void {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($urlLoc, $node, (string) $invocation),
                        2 => self::assertSame($urlLastMod, $node, (string) $invocation),
                        default => self::assertSame($urlChangefreq, $node, (string) $invocation),
                    };
                },
            );

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(5);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $time, $changefreq, $urlLoc, $urlLastMod, $urlChangefreq): DOMElement {
                    $invocation = $matcher->numberOfInvocations();

                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace, (string) $invocation);

                    match ($invocation) {
                        1 => self::assertSame('urlset', $qualifiedName, (string) $invocation),
                        2 => self::assertSame('url', $qualifiedName, (string) $invocation),
                        3 => self::assertSame('loc', $qualifiedName, (string) $invocation),
                        4 => self::assertSame('lastmod', $qualifiedName, (string) $invocation),
                        default => self::assertSame('changefreq', $qualifiedName, (string) $invocation),
                    };

                    match ($invocation) {
                        3 => self::assertSame(
                            $serverUrl . '-test-' . $parentUri,
                            $value,
                            (string) $invocation,
                        ),
                        4 => self::assertSame(date('c', $time), $value, (string) $invocation),
                        5 => self::assertSame($changefreq, $value, (string) $invocation),
                        default => self::assertSame('', $value, (string) $invocation),
                    };

                    return match ($invocation) {
                        1 => $urlSet,
                        2 => $urlNode,
                        3 => $urlLoc,
                        4 => $urlLastMod,
                        default => $urlChangefreq,
                    };
                },
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

        $locValidator = $this->createMock(Loc::class);
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . '-test-' . $parentUri)
            ->willReturn(true);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->createMock(Lastmod::class);
        $lastmodValidator->expects(self::once())
            ->method('isValid')
            ->with(date('c', $time))
            ->willReturn(true);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willReturn(true);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
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
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($container)
            ->willReturn($container);

        $helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePath,
            escaper: $escaper,
            serverUrlHelper: $serverUrlHelper,
        );

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        $helper($container);

        self::assertSame($container, $helper->getContainer());
    }
}
