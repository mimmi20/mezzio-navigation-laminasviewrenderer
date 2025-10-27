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

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Laminas\I18n\View\Helper\Translate;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mimmi20\Mezzio\Navigation\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\Mezzio\Navigation\Page\Route;
use Mimmi20\Mezzio\Navigation\Page\Uri;
use Override;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Throwable;

use function assert;
use function sprintf;

final class BreadcrumbsTest extends TestCase
{
    /** @throws void */
    #[Override]
    protected function tearDown(): void
    {
        Breadcrumbs::setDefaultAuthorization(null);
        Breadcrumbs::setDefaultRole(null);
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        self::assertSame(1, $helper->getMinDepth());

        $helper->setMinDepth(4);

        self::assertSame(4, $helper->getMinDepth());

        $helper->setMinDepth(-1);

        self::assertSame(1, $helper->getMinDepth());

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        self::assertSame([], $helper->getRoles());
        self::assertFalse($helper->hasRoles());

        Breadcrumbs::setDefaultRole($defaultRole);

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        assert($defaultAuth instanceof AuthorizationInterface);
        Breadcrumbs::setDefaultAuthorization($defaultAuth);

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException(new InvalidArgumentException('test'));

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetPartial(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        self::assertNull($helper->getPartial());

        $helper->setPartial('test');

        self::assertSame('test', $helper->getPartial());

        $helper->setPartial(1);

        self::assertSame('test', $helper->getPartial());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetLinkLast(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        self::assertFalse($helper->getLinkLast());

        $helper->setLinkLast(true);

        self::assertTrue($helper->getLinkLast());

        $helper->setLinkLast(false);

        self::assertFalse($helper->getLinkLast());
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetSeparator(): void
    {
        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        self::assertSame(' &gt; ', $helper->getSeparator());

        $helper->setSeparator('/');

        self::assertSame('/', $helper->getSeparator());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
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
            ->with(Breadcrumbs::class, $page)
            ->willReturn($expected);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testHtmlifyWithException(): void
    {
        $exception = new \Laminas\I18n\Exception\RuntimeException('test');

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
            ->with(Breadcrumbs::class, $page)
            ->willThrowException($exception);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->htmlify($page);
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindActiveNoActivePages(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = new Route();
        $page->setActive(false);

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindActiveWithContainerException(): void
    {
        $name      = 'Mezzio\Navigation\Top';
        $exception = new \Laminas\Stdlib\Exception\InvalidArgumentException('test');

        $page = new Route();
        $page->setActive(false);

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException($exception);

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        try {
            $helper->findActive($name, $minDepth, $maxDepth);

            self::fail('Exception expected');
        } catch (Throwable $e) {
            self::assertInstanceOf(InvalidArgumentException::class, $e);

            self::assertSame('test', $e->getMessage());
            self::assertSame(0, $e->getCode());
            self::assertSame($exception, $e->getPrevious());
        }
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFindActiveNoActivePages2(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = new Route();
        $page->setActive(false);

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn(null);

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testAcceptWithoutRoles(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\Navigation\Top';

        $resource = 'test-resource';

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $page->expects(self::once())
            ->method('getPrivilege')
            ->willReturn(null);
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
        $auth->expects(self::once())
            ->method('isGranted')
            ->with(null, $resource, null, null)
            ->willReturn(false);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        $helper->setContainer($name);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setUseAuthorization();

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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRenderStraightNoActivePage(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $page = new Route();
        $page->setVisible(false);

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($name, $containerParam, (string) $invocation),
                        default => self::assertSame($container, $containerParam, (string) $invocation),
                    };

                    return $container;
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected  = '';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRenderStraight(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setActive(true);
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel('parent-label');
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = new Uri();
        $page->setActive(true);
        $page->setUri('###');
        $page->setId('id');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege, null)
            ->willReturn(true);

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';
        $expected2 = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $matcher = self::exactly(2);
        $htmlify->expects($matcher)
            ->method('toHtml')
            ->willReturnCallback(
                static function (
                    string $prefix,
                    PageInterface $pageParam,
                    bool $escapeLabel = true,
                    bool $addClassToListItem = false,
                    array $attributes = [],
                    bool $convertToButton = false,
                ) use (
                    $matcher,
                    $page,
                    $parentPage,
                    $expected2,
                    $expected1,
                ): string {
                    $invocation = $matcher->numberOfInvocations();

                    self::assertSame(Breadcrumbs::class, $prefix, (string) $invocation);

                    match ($invocation) {
                        1 => self::assertSame($page, $pageParam, (string) $invocation),
                        default => self::assertSame($parentPage, $pageParam, (string) $invocation),
                    };

                    self::assertTrue($escapeLabel, (string) $invocation);
                    self::assertFalse($addClassToListItem, (string) $invocation);
                    self::assertSame([], $attributes, (string) $invocation);
                    self::assertFalse($convertToButton, (string) $invocation);

                    return match ($invocation) {
                        1 => $expected2,
                        default => $expected1,
                    };
                },
            );

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($name, $containerParam, (string) $invocation),
                        default => self::assertSame($container, $containerParam, (string) $invocation),
                    };

                    return $container;
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected  = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>/<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setUseAuthorization();

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);
        $helper->setMinDepth(0);

        self::assertSame($expected, $helper->renderStraight($name));
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRenderStraightWithContainerException(): void
    {
        $name      = 'Mezzio\Navigation\Top';
        $exception = new \Laminas\Stdlib\Exception\InvalidArgumentException('test');

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setActive(true);
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel('parent-label');
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = new Uri();
        $page->setActive(true);
        $page->setUri('###');
        $page->setId('id');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException($exception);

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setUseAuthorization();

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);
        $helper->setMinDepth(0);

        try {
            $helper->renderStraight($name);

            self::fail('Exception expected');
        } catch (Throwable $e) {
            self::assertInstanceOf(InvalidArgumentException::class, $e);

            self::assertSame('test', $e->getMessage());
            self::assertSame(0, $e->getCode());
            self::assertSame($exception, $e->getPrevious());
        }
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRenderStraightWithHtmlException(): void
    {
        $name      = 'Mezzio\Navigation\Top';
        $exception = new \Laminas\I18n\Exception\RuntimeException('test');

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setActive(true);
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel('parent-label');
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = new Uri();
        $page->setActive(true);
        $page->setUri('###');
        $page->setId('id');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege, null)
            ->willReturn(true);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Breadcrumbs::class, $page, true, false, [], false)
            ->willThrowException($exception);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($name, $containerParam, (string) $invocation),
                        default => self::assertSame($container, $containerParam, (string) $invocation),
                    };

                    return $container;
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setUseAuthorization();

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);
        $helper->setMinDepth(0);

        try {
            $helper->renderStraight($name);

            self::fail('Exception expected');
        } catch (Throwable $e) {
            self::assertInstanceOf(RuntimeException::class, $e);

            self::assertSame('test', $e->getMessage());
            self::assertSame(0, $e->getCode());
            self::assertSame($exception, $e->getPrevious());
        }
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRenderStraightWithTranslatorException(): void
    {
        $name      = 'Mezzio\Navigation\Top';
        $exception = new \Laminas\I18n\Exception\RuntimeException('test');

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $label = 'test-label';

        $parentPage = new Uri();
        $parentPage->setActive(true);
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setId('parent-id');
        $parentPage->setClass('parent-class');
        $parentPage->setUri('##');
        $parentPage->setTarget('self');
        $parentPage->setLabel('parent-label');
        $parentPage->setTitle('parent-title');
        $parentPage->setTextDomain('parent-text-domain');

        $page = new Uri();
        $page->setActive(true);
        $page->setUri('###');
        $page->setId('id');
        $page->setLabel($label);

        $parentPage->addPage($page);

        $container = new Navigation();
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        1 => self::assertSame($name, $containerParam, (string) $invocation),
                        default => self::assertSame($container, $containerParam, (string) $invocation),
                    };

                    return $container;
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::once())
            ->method('__invoke')
            ->with($label, null, null)
            ->willThrowException($exception);

        $helper = new Breadcrumbs(
            htmlify: $htmlify,
            containerParser: $containerParser,
            escaper: $escapePlugin,
            renderer: $renderer,
            translator: $translatePlugin,
        );

        $helper->setRoles([$role]);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(false);
        $helper->setUseAuthorization();

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);
        $helper->setMinDepth(0);

        try {
            $helper->renderStraight($name);

            self::fail('Exception expected');
        } catch (Throwable $e) {
            self::assertInstanceOf(RuntimeException::class, $e);

            self::assertSame('test', $e->getMessage());
            self::assertSame(0, $e->getCode());
            self::assertSame($exception, $e->getPrevious());
        }
    }
}
