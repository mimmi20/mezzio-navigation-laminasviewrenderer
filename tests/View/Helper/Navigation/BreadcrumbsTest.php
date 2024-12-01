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

use Laminas\I18n\View\Helper\Translate;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Breadcrumbs::setDefaultRole($defaultRole);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testDoNotAcceptWithException(): void
    {
        $exception = new ServiceNotFoundException('test');

        $container = $this->createMock(ContainerInterface::class);
        $name      = 'Mezzio\Navigation\Top';

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');

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
            ->willThrowException($exception);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->accept($page);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
     * @throws ExceptionInterface
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testFindActiveWithoutContainer(): void
    {
        $role     = 'testRole';
        $maxDepth = 42;
        $minDepth = 1;

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with(null)
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveNoActivePageWithoutDepth(): void
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
        $minDepth = 1;

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        $helper->setMinDepth($minDepth);
        $helper->setMaxDepth($maxDepth);

        self::assertSame($expected, $helper->findActive($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
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
            ->with($container, 1, $maxDepth)
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindActiveException(): void
    {
        $exception = new ServiceNotFoundException('test');

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
            ->willThrowException($exception);

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setMinDepth(-1);
        $helper->setMaxDepth($maxDepth);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->findActive($name);
    }

    /** @throws Exception */
    public function testSetPartial(): void
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        self::assertNull($helper->getPartial());

        $helper->setPartial('test');

        self::assertSame('test', $helper->getPartial());

        $helper->setPartial(1);

        self::assertSame('test', $helper->getPartial());
    }

    /** @throws Exception */
    public function testSetLinkLast(): void
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        self::assertFalse($helper->getLinkLast());

        $helper->setLinkLast(true);

        self::assertTrue($helper->getLinkLast());

        $helper->setLinkLast(false);

        self::assertFalse($helper->getLinkLast());
    }

    /** @throws Exception */
    public function testSetSeparator(): void
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        self::assertSame(' &gt; ', $helper->getSeparator());

        $helper->setSeparator('/');

        self::assertSame('/', $helper->getSeparator());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithParamsWithoutPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: No partial view script provided');
        $this->expectExceptionCode(0);

        $helper->renderPartialWithParams(['abc' => 'test'], $name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithParamsWithWrongPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unable to render breadcrumbs: A view partial supplied as an array must contain one value: the partial view script',
        );
        $this->expectExceptionCode(0);

        $helper->renderPartialWithParams(['abc' => 'test'], $name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithParams(): void
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
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('isActive');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 2,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return $container;
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with(
                $partial,
                ['abc' => 'test', 'pages' => [$parentPage, $page], 'separator' => $seperator],
            )
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], $name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithParamsAndArrayPartial(): void
    {
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
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('isActive');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 2,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(3);
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

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with(
                $partial,
                ['pages' => [$parentPage, $page], 'separator' => $seperator, 'abc' => 'test'],
            )
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($container);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderPartialWithParams(['abc' => 'test'], null, [$partial, 'test']),
        );
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithParamsAndArrayPartialRenderingPage(): void
    {
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setActive(true);

        $page = new Uri();
        $page->setVisible(true);
        $page->setResource($resource);
        $page->setPrivilege($privilege);
        $page->setActive(true);

        $subPage = $this->createMock(PageInterface::class);
        $subPage->expects(self::never())
            ->method('isVisible');
        $subPage->expects(self::never())
            ->method('getResource');
        $subPage->expects(self::never())
            ->method('getPrivilege');
        $subPage->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $subPage->expects(self::never())
            ->method('isActive');

        assert(
            $subPage instanceof PageInterface,
            sprintf(
                '$subPage should be an Instance of %s, but was %s',
                PageInterface::class,
                $subPage::class,
            ),
        );
        $page->addPage($subPage);
        $parentPage->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($parentPage, 1, null)
            ->willReturn(
                [
                    'page' => $subPage,
                    'depth' => 2,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $parentPage): ContainerInterface | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($parentPage, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $parentPage,
                    };
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with(
                $partial,
                ['pages' => [$parentPage, $subPage], 'separator' => $seperator, 'abc' => 'test'],
            )
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderPartialWithParams(['abc' => 'test'], null, [$partial, 'test']),
        );
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithParamsNoActivePage(): void
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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return $container;
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [], 'separator' => $seperator, 'abc' => 'test'])
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], $name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithoutPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: No partial view script provided');
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testRenderPartialWithWrongPartial(): void
    {
        $name = 'Mezzio\Navigation\Top';

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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unable to render breadcrumbs: A view partial supplied as an array must contain one value: the partial view script',
        );
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderPartial(): void
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
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('isActive');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 2,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return $container;
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [$parentPage, $page], 'separator' => $seperator])
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderPartialNoActivePage(): void
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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return $container;
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [], 'separator' => $seperator])
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithArrayPartial(): void
    {
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
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $page->expects(self::never())
            ->method('isActive');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 2,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(3);
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

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial   = 'testPartial';
        $expected  = 'renderedPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [$parentPage, $page], 'separator' => $seperator])
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($container);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithArrayPartialRenderingPage(): void
    {
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setActive(true);

        $page = new Uri();
        $page->setVisible(true);
        $page->setResource($resource);
        $page->setPrivilege($privilege);
        $page->setActive(true);

        $subPage = $this->createMock(PageInterface::class);
        $subPage->expects(self::never())
            ->method('isVisible');
        $subPage->expects(self::never())
            ->method('getResource');
        $subPage->expects(self::never())
            ->method('getPrivilege');
        $subPage->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $subPage->expects(self::never())
            ->method('isActive');

        assert(
            $subPage instanceof PageInterface,
            sprintf(
                '$subPage should be an Instance of %s, but was %s',
                PageInterface::class,
                $subPage::class,
            ),
        );
        $page->addPage($subPage);
        $parentPage->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($parentPage, 1, null)
            ->willReturn(
                [
                    'page' => $subPage,
                    'depth' => 2,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $parentPage): ContainerInterface | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($parentPage, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $parentPage,
                    };
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [$parentPage, $subPage], 'separator' => $seperator])
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderPartialWithPartialModel(): void
    {
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
        $parentPage->setVisible(true);
        $parentPage->setResource($resource);
        $parentPage->setPrivilege($privilege);
        $parentPage->setActive(true);

        $page = new Uri();
        $page->setVisible(true);
        $page->setResource($resource);
        $page->setPrivilege($privilege);
        $page->setActive(true);

        $subPage = $this->createMock(PageInterface::class);
        $subPage->expects(self::never())
            ->method('isVisible');
        $subPage->expects(self::never())
            ->method('getResource');
        $subPage->expects(self::never())
            ->method('getPrivilege');
        $subPage->expects(self::once())
            ->method('getParent')
            ->willReturn($parentPage);
        $subPage->expects(self::never())
            ->method('isActive');

        assert(
            $subPage instanceof PageInterface,
            sprintf(
                '$subPage should be an Instance of %s, but was %s',
                PageInterface::class,
                $subPage::class,
            ),
        );
        $page->addPage($subPage);
        $parentPage->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($parentPage, 1, null)
            ->willReturn(
                [
                    'page' => $subPage,
                    'depth' => 2,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $parentPage): ContainerInterface | null {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($parentPage, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $parentPage,
                    };
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $expected  = 'renderedPartial';
        $seperator = '/';
        $data      = ['pages' => [$parentPage, $subPage], 'separator' => $seperator];

        $model = $this->createMock(ModelInterface::class);
        $model->expects(self::never())
            ->method('setVariables');
        $model->expects(self::never())
            ->method('getTemplate');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($model, $data)
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial(null, $model));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderStraightNoActivePage(): void
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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerParam),
                        default => self::assertSame($container, $containerParam),
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

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
     * @throws ExceptionInterface
     */
    public function testRenderStraight(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
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

        $page = $this->createMock(PageInterface::class);
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
            ->willReturn(false);
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

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
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
                    self::assertSame(Breadcrumbs::class, $prefix);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page, $pageParam),
                        default => self::assertSame($parentPage, $pageParam),
                    };

                    self::assertTrue($escapeLabel);
                    self::assertFalse($addClassToListItem);
                    self::assertSame([], $attributes);
                    self::assertFalse($convertToButton);

                    return match ($matcher->numberOfInvocations()) {
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
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerParam),
                        default => self::assertSame($container, $containerParam),
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected  = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>/<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderStraightWithoutLinkAtEnd(): void
    {
        $resource               = 'testResource';
        $privilege              = 'testPrivilege';
        $label                  = 'testLabel';
        $tranalatedLabel        = 'testLabelTranslated';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $textDomain             = 'testDomain';

        $parentPage = new Uri();
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

        $page = $this->createMock(PageInterface::class);
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
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($label);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($textDomain);
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

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
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

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Breadcrumbs::class, $parentPage)
            ->willReturn($expected1);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(3);
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

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::once())
            ->method('__invoke')
            ->with($tranalatedLabel)
            ->willReturn($escapedTranalatedLabel);

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::once())
            ->method('__invoke')
            ->with($label, $textDomain)
            ->willReturn($tranalatedLabel);

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);
        $helper->setContainer($container);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected  = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>/testLabelTranslatedAndEscaped';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(false);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderStraightWithoutLinkAtEnd2(): void
    {
        $indent = '    ';

        $resource               = 'testResource';
        $privilege              = 'testPrivilege';
        $label                  = 'testLabel';
        $tranalatedLabel        = 'testLabelTranslated';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $textDomain             = 'testDomain';

        $parentPage = new Uri();
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

        $page = $this->createMock(PageInterface::class);
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
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($label);
        $page->expects(self::once())
            ->method('getTextDomain')
            ->willReturn($textDomain);
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

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
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

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Breadcrumbs::class, $parentPage)
            ->willReturn($expected1);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(3);
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

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::once())
            ->method('__invoke')
            ->with($tranalatedLabel)
            ->willReturn($escapedTranalatedLabel);

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::once())
            ->method('__invoke')
            ->with($label, $textDomain)
            ->willReturn($tranalatedLabel);

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);
        $helper->setContainer($container);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected  = $indent . '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>/testLabelTranslatedAndEscaped';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(false);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);
        $helper->setIndent($indent);

        self::assertSame($expected, $helper->renderStraight());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderWithoutPartial(): void
    {
        $name      = 'Mezzio\Navigation\Top';
        $resource  = 'testResource';
        $privilege = 'testPrivilege';

        $parentPage = new Uri();
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

        $page = $this->createMock(PageInterface::class);
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
            ->willReturn(false);
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

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

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
                    self::assertSame(Breadcrumbs::class, $prefix);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page, $pageParam),
                        default => self::assertSame($parentPage, $pageParam),
                    };

                    self::assertTrue($escapeLabel);
                    self::assertFalse($addClassToListItem);
                    self::assertSame([], $attributes);
                    self::assertFalse($convertToButton);

                    return match ($matcher->numberOfInvocations()) {
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
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerParam),
                        default => self::assertSame($container, $containerParam),
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected  = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>/<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);

        $view = $this->createMock(PhpRenderer::class);
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testRenderWithPartial(): void
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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn([]);

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return $container;
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [], 'separator' => $seperator])
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->createMock(PhpRenderer::class);
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
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testToStringWithPartial(): void
    {
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');
        assert($auth instanceof AuthorizationInterface);

        $name = 'Mezzio\Navigation\Top';

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn([]);

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(3);
        $containerParser->expects($matcher)
            ->method('parseContainer')
            ->willReturnCallback(
                static function (ContainerInterface | string | null $containerParam) use ($matcher, $name, $container): ContainerInterface | null {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($name, $containerParam),
                        2 => self::assertNull($containerParam),
                        default => self::assertSame($container, $containerParam),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => null,
                        default => $container,
                    };
                },
            );

        $escapePlugin = $this->createMock(EscapeHtml::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['pages' => [], 'separator' => $seperator])
            ->willReturn($expected);

        $translatePlugin = $this->createMock(Translate::class);
        $translatePlugin->expects(self::never())
            ->method('__invoke');

        $helper = new Breadcrumbs(
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setRole($role);
        $helper->setAuthorization($auth);
        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, (string) $helper($name));
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($container)
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        $helper($container);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testDoNotRenderIfNoPageIsActive(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(self::never())
            ->method('isVisible');
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn([]);

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
                    'authorization' => null,
                    'renderInvisible' => false,
                    'role' => null,
                ],
            )
            ->willReturn($findActiveHelper);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(3);
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
            $serviceLocator,
            $htmlify,
            $containerParser,
            $escapePlugin,
            $renderer,
            $translatePlugin,
        );

        $helper->setContainer($container);

        self::assertSame('', $helper->render());
    }
}
