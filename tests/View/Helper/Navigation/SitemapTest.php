<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMNode;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Uri\Exception\InvalidUriException;
use Laminas\Uri\Exception\InvalidUriPartException;
use Laminas\Uri\UriInterface;
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
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use PHPUnit\Framework\Constraint\IsInstanceOf;
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
        Sitemap::setDefaultAuthorization(null);
        Sitemap::setDefaultRole(null);
    }

    /** @throws Exception */
    public function testSetMaxDepth(): void
    {
        $maxDepth = 4;

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /** @throws Exception */
    public function testSetMinDepth(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
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

    /** @throws Exception */
    public function testSetRenderInvisible(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /** @throws Exception */
    public function testSetRole(): void
    {
        $role        = 'testRole';
        $defaultRole = 'testDefaultRole';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Sitemap::setDefaultRole($defaultRole);

        self::assertSame($defaultRole, $helper->getRole());
        self::assertTrue($helper->hasRole());

        $helper->setRole($role);

        self::assertSame($role, $helper->getRole());
        self::assertTrue($helper->hasRole());
    }

    /** @throws Exception */
    public function testSetUseAuthorization(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());

        $helper->setUseAuthorization();

        self::assertTrue($helper->getUseAuthorization());
    }

    /** @throws Exception */
    public function testSetAuthorization(): void
    {
        $auth        = $this->createMock(AuthorizationInterface::class);
        $defaultAuth = $this->createMock(AuthorizationInterface::class);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
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

    /** @throws Exception */
    public function testSetView(): void
    {
        $view = $this->createMock(RendererInterface::class);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertNull($helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
        self::assertSame($serviceLocator, $helper->getServiceLocator());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
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
     */
    public function testSetContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $name = 'default';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetContainerWithStringFound(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\Navigation\Top';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testDoNotAccept(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\Navigation\Top';

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(false);

        $auth = $this->createMock(AuthorizationInterface::class);

        $role = 'testRole';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
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
                $page::class,
            ),
        );
        self::assertFalse($helper->accept($page));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testHtmlify(): void
    {
        $expected = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslatedAndEscaped</a>';

        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\Navigation\Top';

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
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

    /** @throws Exception */
    public function testSetIndent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
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
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindActiveNoActivePages(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $parentPage = $this->createMock(PageInterface::class);
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
            ->method('isActive');

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($findActiveHelper);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePage(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $parentPage = $this->createMock(PageInterface::class);
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
            ->method('isActive');

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 0,
                ],
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($findActiveHelper);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
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
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testFindActiveWithoutContainer(): void
    {
        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with(new IsInstanceOf(Navigation::class), $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($findActiveHelper);

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
            ->with(null)
            ->willReturn(null);

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageWithoutDepth(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $parentPage = $this->createMock(PageInterface::class);
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
            ->method('isActive');

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 0;

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 0,
                ],
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($findActiveHelper);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
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
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageOutOfRange(): void
    {
        $name = 'Mezzio\Navigation\Top';

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
            ->method('isActive');

        $container = new Navigation();
        $container->addPage($page);

        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 2;

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($findActiveHelper);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, 2, 42));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageRecursive(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);

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
            ->method('isActive');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role     = 'testRole';
        $maxDepth = 0;
        $minDepth = 0;

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn(
                [
                    'page' => $parentPage,
                    'depth' => 0,
                ],
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($findActiveHelper);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
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
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageRecursive2(): void
    {
        $name = 'Mezzio\Navigation\Top';

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

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($findActiveHelper);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindActiveOneActivePageRecursive3(): void
    {
        $name = 'Mezzio\Navigation\Top';

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

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, $maxDepth)
            ->willReturn([]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($findActiveHelper);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setMinDepth(-1);
        $helper->setMaxDepth($maxDepth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name));
    }

    /** @throws Exception */
    public function testSetUseXmlDeclaration(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertTrue($helper->getUseXmlDeclaration());

        $helper->setUseXmlDeclaration(false);

        self::assertFalse($helper->getUseXmlDeclaration());
    }

    /** @throws Exception */
    public function testSetUseSchemaValidation(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertFalse($helper->getUseSchemaValidation());

        $helper->setUseSchemaValidation(true);

        self::assertTrue($helper->getUseSchemaValidation());
    }

    /** @throws Exception */
    public function testSetUseSitemapValidators(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertTrue($helper->getUseSitemapValidators());

        $helper->setUseSitemapValidators(false);

        self::assertFalse($helper->getUseSitemapValidators());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetInvalidServerUrl(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $uri = 'ftp://test.org';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid server URL');
        $this->expectExceptionCode(0);

        $helper->setServerUrl($uri);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetServerUrlWithInvalidFragment(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $uri = $this->createMock(UriInterface::class);
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
     * @throws InvalidArgumentException
     */
    public function testSetServerUrlWithInvalidUri(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $uri = $this->createMock(UriInterface::class);
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
     * @throws InvalidArgumentException
     */
    public function testSetServerUrlWithError(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $uri = $this->createMock(UriInterface::class);
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
     * @throws InvalidArgumentException
     */
    public function testSetServerUrl(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $serverUrl = 'ftp://test.org';

        $uri = $this->createMock(UriInterface::class);
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

    /** @throws Exception */
    public function testSetFormatOutput(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertFalse($helper->getFormatOutput());

        $helper->setFormatOutput(true);

        self::assertTrue($helper->getFormatOutput());
    }

    /** @throws Exception */
    public function testGetServerUrl(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'ftp://test.org';

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->willReturn($serverUrl);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertSame($serverUrl, $helper->getServerUrl());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testUrlWithoutPageHref(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn('');

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertSame('', $helper->url($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testUrlWithRelativePageHref(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'ftp://test.org';
        $uri       = '/';

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $uri)
            ->willReturn($serverUrl . '/' . $uri);

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->willReturn($serverUrl);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($uri);

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertSame($serverUrl . '/' . $uri, $helper->url($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testUrlWithAbsolutePageHref(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $uri = 'ftp://test.org';

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($uri)
            ->willReturn($uri . '/');

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::never())
            ->method('__invoke');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::exactly(2))
            ->method('getHref')
            ->willReturn($uri);

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertSame($uri . '/', $helper->url($page));
        self::assertSame('', $helper->url($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testUrlWithRelativePageHref2(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $baseUri = '/test';

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::once())
            ->method('__invoke')
            ->willReturn($baseUri);

        $serverUrl = 'ftp://test.org';
        $uri       = 'test.html';

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $baseUri . '/' . $uri)
            ->willReturn($serverUrl . '/' . $baseUri . '/' . $uri);

        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $serverUrlHelper->expects(self::once())
            ->method('__invoke')
            ->willReturn($serverUrl);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($uri);

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        self::assertSame($serverUrl . '/' . $baseUri . '/' . $uri, $helper->url($page));
    }

    /** @throws Exception */
    public function testSetDomDocument(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $dom = $this->createMock(DOMDocument::class);

        self::assertInstanceOf(DOMDocument::class, $helper->getDom());

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        self::assertSame($dom, $helper->getDom());
    }

    /** @throws Exception */
    public function testSetLocValidator(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $locValidator = $this->createMock(Loc::class);

        self::assertInstanceOf(Loc::class, $helper->getLocValidator());

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        self::assertSame($locValidator, $helper->getLocValidator());
    }

    /** @throws Exception */
    public function testSetLastmodValidator(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $lastmodValidator = $this->createMock(Lastmod::class);

        self::assertInstanceOf(Lastmod::class, $helper->getLastmodValidator());

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        self::assertSame($lastmodValidator, $helper->getLastmodValidator());
    }

    /** @throws Exception */
    public function testSetPriorityValidator(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $priorityValidator = $this->createMock(Priority::class);

        self::assertInstanceOf(Priority::class, $helper->getPriorityValidator());

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($priorityValidator, $helper->getPriorityValidator());
    }

    /** @throws Exception */
    public function testSetChangefreqValidator(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $changefreqValidator = $this->createMock(Changefreq::class);

        self::assertInstanceOf(Changefreq::class, $helper->getChangefreqValidator());

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        self::assertSame($changefreqValidator, $helper->getChangefreqValidator());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursive(): void
    {
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);

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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getHref');

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );
        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org';

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . '/' . $parentUri);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(0);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(3);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $urlLoc): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        default => self::assertSame('loc', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '/' . $parentUri, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        default => $urlLoc,
                    };
                },
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
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveWithSchemaValidation(): void
    {
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentUri = '/test.html';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setUri($parentUri);

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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getHref');

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );
        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org';

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . '/' . $parentUri);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(0);
        $helper->setUseSchemaValidation(true);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(3);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $urlLoc): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        default => self::assertSame('loc', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '/' . $parentUri, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        default => $urlLoc,
                    };
                },
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
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeep(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $page),
                        2 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        2 => false,
                        default => true,
                    };
                },
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $basePath = $this->createMock(BasePath::class);
        $basePath->expects(self::never())
            ->method('__invoke');

        $serverUrl = 'http://test.org';

        $escaper = $this->createMock(EscapeHtml::class);
        $escaper->expects(self::once())
            ->method('__invoke')
            ->with($serverUrl . $parentUri)
            ->willReturn($serverUrl . '/' . $parentUri);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(3);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $urlLoc): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        default => self::assertSame('loc', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '/' . $parentUri, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        default => $urlLoc,
                    };
                },
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
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLocValidation(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
            ->willReturn($serverUrl . 'test' . $parentUri);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::never())
            ->method('appendChild');

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(2);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $urlSet, $urlNode): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        default => self::assertSame('url', $qualifiedName),
                    };

                    self::assertSame('', $value);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        default => $urlNode,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->createMock(Loc::class);
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willReturn(false);

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Encountered an invalid URL for Sitemap XML: "%s"',
                $serverUrl . 'test' . $parentUri,
            ),
        );
        $this->expectExceptionCode(0);

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLocValidationException(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
            ->willReturn($serverUrl . 'test' . $parentUri);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::never())
            ->method('appendChild');

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(2);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $urlSet, $urlNode): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        default => self::assertSame('url', $qualifiedName),
                    };

                    self::assertSame('', $value);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        default => $urlNode,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->createMock(Loc::class);
        $locValidator->expects(self::once())
            ->method('isValid')
            ->with($serverUrl . 'test' . $parentUri)
            ->willThrowException(new \Laminas\Validator\Exception\RuntimeException('test'));

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'An error occured while validating an URL for Sitemap XML: "%s"',
                $serverUrl . 'test' . $parentUri,
            ),
        );
        $this->expectExceptionCode(0);

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLastmod(): void
    {
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

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $page),
                        2 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        2 => false,
                        default => true,
                    };
                },
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

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

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(4);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlLastMod, $urlChangefreq, $urlPriority): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($urlLoc, $node),
                        2 => self::assertSame($urlLastMod, $node),
                        3 => self::assertSame($urlChangefreq, $node),
                        default => self::assertSame($urlPriority, $node),
                    };
                },
            );

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(6);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $time, $changefreq, $priority, $urlLoc, $urlLastMod, $urlChangefreq, $urlPriority): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        4 => self::assertSame('lastmod', $qualifiedName),
                        5 => self::assertSame('changefreq', $qualifiedName),
                        default => self::assertSame('priority', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame(date('c', $time), $value),
                        5 => self::assertSame($changefreq, $value),
                        6 => self::assertSame($priority, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        3 => $urlLoc,
                        4 => $urlLastMod,
                        5 => $urlChangefreq,
                        default => $urlPriority,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
            ->willReturn(true);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithInvalidLastmod(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $page),
                        2 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        2 => false,
                        default => true,
                    };
                },
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

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

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(3);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlChangefreq, $urlPriority): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($urlLoc, $node),
                        2 => self::assertSame($urlChangefreq, $node),
                        default => self::assertSame($urlPriority, $node),
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
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $priority, $urlLoc, $urlChangefreq, $urlPriority, $changefreq): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        4 => self::assertSame('changefreq', $qualifiedName),
                        default => self::assertSame('priority', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame($changefreq, $value),
                        5 => self::assertSame($priority, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        3 => $urlLoc,
                        4 => $urlChangefreq,
                        default => $urlPriority,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
            ->willReturn(false);

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
            ->willReturn(true);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLastmodException(): void
    {
        $exception = new \Laminas\Validator\Exception\RuntimeException('test');

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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc  = $this->createMock(DOMElement::class);
        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(3);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $urlLoc): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        default => self::assertSame('loc', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        default => $urlLoc,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
            ->willThrowException($exception);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::never())
            ->method('isValid')
            ->with($changefreq);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid')
            ->with($priority);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithInvalidLastmodAndChangeFreq(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $page),
                        2 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        2 => false,
                        default => true,
                    };
                },
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(3);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $urlLoc): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        default => self::assertSame('loc', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        default => $urlLoc,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
            ->willReturn(false);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::once())
            ->method('isValid')
            ->with($changefreq)
            ->willReturn(false);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
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
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithLastmodExceptionAndChangeFreqException(): void
    {
        $exception1 = new \Laminas\Validator\Exception\RuntimeException('test');

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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(3);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $urlLoc): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        default => self::assertSame('loc', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        default => $urlLoc,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
            ->willThrowException($exception1);

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::never())
            ->method('isValid')
            ->with($changefreq);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid')
            ->with($priority);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapOneActivePageRecursiveDeepWithoutPriority(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $page),
                        2 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        2 => false,
                        default => true,
                    };
                },
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

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

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(3);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlLastMod, $urlChangefreq): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($urlLoc, $node),
                        2 => self::assertSame($urlLastMod, $node),
                        default => self::assertSame($urlChangefreq, $node),
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
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        4 => self::assertSame('lastmod', $qualifiedName),
                        default => self::assertSame('changefreq', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame(date('c', $time), $value),
                        5 => self::assertSame($changefreq, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
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

        self::assertSame($dom, $helper->getDomSitemap());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException(): void
    {
        $exception = new DOMException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $container): ContainerInterface | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $dom = $this->createMock(DOMDocument::class);
        $dom->expects(self::once())
            ->method('createElementNS')
            ->willThrowException($exception);
        $dom->expects(self::never())
            ->method('appendChild');
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->createMock(Loc::class);
        $locValidator->expects(self::never())
            ->method('isValid');

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->createMock(Lastmod::class);
        $lastmodValidator->expects(self::never())
            ->method('isValid');

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::never())
            ->method('isValid');

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid');

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException2(): void
    {
        $exception = new \Laminas\Stdlib\Exception\InvalidArgumentException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            ->with(null)
            ->willThrowException($exception);

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $dom = $this->createMock(DOMDocument::class);
        $dom->expects(self::never())
            ->method('createElementNS');
        $dom->expects(self::never())
            ->method('appendChild');
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->createMock(Loc::class);
        $locValidator->expects(self::never())
            ->method('isValid');

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->createMock(Lastmod::class);
        $lastmodValidator->expects(self::never())
            ->method('isValid');

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::never())
            ->method('isValid');

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid');

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException3(): void
    {
        $exception = new DOMException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::never())
            ->method('appendChild');

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::never())
            ->method('appendChild');

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(2);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value) use ($matcher, $urlSet, $exception): DOMElement {
                    $invocation = $matcher->numberOfInvocations();

                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($invocation) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        default => self::assertSame('url', $qualifiedName),
                    };

                    self::assertSame('', $value);

                    return match ($invocation) {
                        1 => $urlSet,
                        default => throw $exception,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->createMock(Loc::class);
        $locValidator->expects(self::never())
            ->method('isValid');

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->createMock(Lastmod::class);
        $lastmodValidator->expects(self::never())
            ->method('isValid');

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::never())
            ->method('isValid');

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid');

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException4(): void
    {
        $exception = new DOMException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::never())
            ->method('appendChild');

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(3);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $exception): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        default => self::assertSame('loc', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        default => throw $exception,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
        $lastmodValidator->expects(self::never())
            ->method('isValid');

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::never())
            ->method('isValid');

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid');

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException5(): void
    {
        $exception = new DOMException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::never())
            ->method('appendChild');

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(3);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $exception): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        default => self::assertSame('loc', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        default => throw $exception,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
        $lastmodValidator->expects(self::never())
            ->method('isValid');

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::never())
            ->method('isValid');

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid');

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException6(): void
    {
        $exception = new DOMException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc = $this->createMock(DOMElement::class);

        $urlNode = $this->createMock(DOMElement::class);
        $urlNode->expects(self::once())
            ->method('appendChild')
            ->with($urlLoc);

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(4);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $urlLoc, $time, $exception): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        default => self::assertSame('lastmod', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame(date('c', $time), $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        3 => $urlLoc,
                        default => throw $exception,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
        $changefreqValidator->expects(self::never())
            ->method('isValid');

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid');

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException7(): void
    {
        $exception = new \Laminas\Validator\Exception\RuntimeException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc     = $this->createMock(DOMElement::class);
        $urlLastMod = $this->createMock(DOMElement::class);

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(2);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlLastMod): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($urlLoc, $node),
                        default => self::assertSame($urlLastMod, $node),
                    };
                },
            );

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(4);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $urlSet, $urlNode, $urlLoc, $urlLastMod, $time): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        default => self::assertSame('lastmod', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame(date('c', $time), $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        3 => $urlLoc,
                        default => $urlLastMod,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
            ->willThrowException($exception);

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid');

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException8(): void
    {
        $exception = new DOMException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $urlLoc     = $this->createMock(DOMElement::class);
        $urlLastMod = $this->createMock(DOMElement::class);

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(2);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlLastMod): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($urlLoc, $node),
                        default => self::assertSame($urlLastMod, $node),
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
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $changefreq, $urlSet, $urlNode, $urlLoc, $urlLastMod, $time, $exception): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        4 => self::assertSame('lastmod', $qualifiedName),
                        default => self::assertSame('changefreq', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame(date('c', $time), $value),
                        5 => self::assertSame($changefreq, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        3 => $urlLoc,
                        4 => $urlLastMod,
                        default => throw $exception,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
        $priorityValidator->expects(self::never())
            ->method('isValid');

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException9(): void
    {
        $exception = new \Laminas\Validator\Exception\RuntimeException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

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

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(3);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlLastMod, $urlChangefreq): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($urlLoc, $node),
                        2 => self::assertSame($urlLastMod, $node),
                        default => self::assertSame($urlChangefreq, $node),
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
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $changefreq, $urlSet, $urlNode, $urlLoc, $urlLastMod, $urlChangefreq, $time): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        4 => self::assertSame('lastmod', $qualifiedName),
                        default => self::assertSame('changefreq', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame(date('c', $time), $value),
                        5 => self::assertSame($changefreq, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
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
            ->willThrowException($exception);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testGetDomSitemapWithException10(): void
    {
        $exception = new DOMException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

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

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(3);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlLastMod, $urlChangefreq): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($urlLoc, $node),
                        2 => self::assertSame($urlLastMod, $node),
                        default => self::assertSame($urlChangefreq, $node),
                    };
                },
            );

        $urlSet = $this->createMock(DOMElement::class);
        $urlSet->expects(self::once())
            ->method('appendChild')
            ->with($urlNode);

        $dom     = $this->createMock(DOMDocument::class);
        $matcher = self::exactly(6);
        $dom->expects($matcher)
            ->method('createElementNS')
            ->willReturnCallback(
                static function (string | null $namespace, string $qualifiedName, string $value = '') use ($matcher, $serverUrl, $parentUri, $changefreq, $priority, $urlSet, $urlNode, $urlLoc, $urlLastMod, $urlChangefreq, $time, $exception): DOMElement {
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        4 => self::assertSame('lastmod', $qualifiedName),
                        5 => self::assertSame('changefreq', $qualifiedName),
                        default => self::assertSame('priority', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame(date('c', $time), $value),
                        5 => self::assertSame($changefreq, $value),
                        6 => self::assertSame((string) $priority, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $urlSet,
                        2 => $urlNode,
                        3 => $urlLoc,
                        4 => $urlLastMod,
                        5 => $urlChangefreq,
                        default => throw $exception,
                    };
                },
            );
        $dom->expects(self::once())
            ->method('appendChild')
            ->with($urlSet);
        $dom->expects(self::never())
            ->method('schemaValidate');

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
            ->willReturn(true);

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        self::expectException(RuntimeException::class);
        self::expectExceptionCode(0);
        self::expectExceptionMessage('test');

        $helper->getDomSitemap();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testRenderWithXmlDeclaration(): void
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $page),
                        2 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        2 => false,
                        default => true,
                    };
                },
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

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

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(3);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlLastMod, $urlChangefreq): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($urlLoc, $node),
                        2 => self::assertSame($urlLastMod, $node),
                        default => self::assertSame($urlChangefreq, $node),
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
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        4 => self::assertSame('lastmod', $qualifiedName),
                        default => self::assertSame('changefreq', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame(date('c', $time), $value),
                        5 => self::assertSame($changefreq, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
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

        self::assertSame($xml, $helper->render());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testRenderWithException(): void
    {
        $exception = new DOMException('test');

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
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $container): ContainerInterface | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);
        $helper->setContainer($container);
        $helper->setFormatOutput(true);
        $helper->setMinDepth(0);
        $helper->setMaxDepth(42);
        $helper->setUseSchemaValidation(false);

        $dom = $this->createMock(DOMDocument::class);
        $dom->expects(self::once())
            ->method('createElementNS')
            ->willThrowException($exception);
        $dom->expects(self::never())
            ->method('appendChild');
        $dom->expects(self::never())
            ->method('schemaValidate');
        $dom->expects(self::never())
            ->method('saveXML');

        assert($dom instanceof DOMDocument);
        $helper->setDom($dom);

        $locValidator = $this->createMock(Loc::class);
        $locValidator->expects(self::never())
            ->method('isValid');

        assert($locValidator instanceof Loc);
        $helper->setLocValidator($locValidator);

        $lastmodValidator = $this->createMock(Lastmod::class);
        $lastmodValidator->expects(self::never())
            ->method('isValid');

        assert($lastmodValidator instanceof Lastmod);
        $helper->setLastmodValidator($lastmodValidator);

        $changefreqValidator = $this->createMock(Changefreq::class);
        $changefreqValidator->expects(self::never())
            ->method('isValid');

        assert($changefreqValidator instanceof Changefreq);
        $helper->setChangefreqValidator($changefreqValidator);

        $priorityValidator = $this->createMock(Priority::class);
        $priorityValidator->expects(self::never())
            ->method('isValid');

        assert($priorityValidator instanceof Priority);
        $helper->setPriorityValidator($priorityValidator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->render();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $page),
                        2 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        2 => false,
                        default => true,
                    };
                },
            );

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($acceptHelper);

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
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $helper = new Sitemap(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

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

        $urlNode = $this->createMock(DOMElement::class);
        $matcher = self::exactly(3);
        $urlNode->expects($matcher)
            ->method('appendChild')
            ->willReturnCallback(
                static function (DOMNode $node) use ($matcher, $urlLoc, $urlLastMod, $urlChangefreq): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($urlLoc, $node),
                        2 => self::assertSame($urlLastMod, $node),
                        default => self::assertSame($urlChangefreq, $node),
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
                    self::assertSame(SitemapInterface::SITEMAP_NS, $namespace);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('urlset', $qualifiedName),
                        2 => self::assertSame('url', $qualifiedName),
                        3 => self::assertSame('loc', $qualifiedName),
                        4 => self::assertSame('lastmod', $qualifiedName),
                        default => self::assertSame('changefreq', $qualifiedName),
                    };

                    match ($matcher->numberOfInvocations()) {
                        3 => self::assertSame($serverUrl . '-test-' . $parentUri, $value),
                        4 => self::assertSame(date('c', $time), $value),
                        5 => self::assertSame($changefreq, $value),
                        default => self::assertSame('', $value),
                    };

                    return match ($matcher->numberOfInvocations()) {
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
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $basePath,
            $escaper,
            $serverUrlHelper,
        );

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        $helper($container);

        self::assertSame($container, $helper->getContainer());
    }
}
