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

use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ConvertToPagesInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\FindRootInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mimmi20\Mezzio\Navigation\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Override;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

use function assert;
use function sprintf;

final class LinksTest extends TestCase
{
    /** @throws void */
    #[Override]
    protected function tearDown(): void
    {
        Links::setDefaultAuthorization(null);
        Links::setDefaultRole(null);
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
        );

        self::assertSame([], $helper->getRoles());
        self::assertFalse($helper->hasRoles());

        Links::setDefaultRole($defaultRole);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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
        $page->expects(self::never())
            ->method('get');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Links::class, $page)
            ->willReturn($expected);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $converter = $this->createMock(ConvertToPagesInterface::class);
        $converter->expects(self::never())
            ->method('convert');

        $helper = new Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $rootFinder,
            headLink: $headLink,
        );

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }
}
