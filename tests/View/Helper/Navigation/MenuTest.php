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

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Helper\Escaper\AbstractHelper;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;
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

use const PHP_EOL;

final class MenuTest extends TestCase
{
    /** @throws void */
    #[Override]
    protected function tearDown(): void
    {
        Menu::setDefaultAuthorization(null);
        Menu::setDefaultRole(null);
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Menu::setDefaultRole($defaultRole);

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        assert($defaultAuth instanceof AuthorizationInterface);
        Menu::setDefaultAuthorization($defaultAuth);

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertNull($helper->getView());

        assert($view instanceof RendererInterface);
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
        self::assertSame($serviceLocator, $helper->getServiceLocator());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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
    public function testSetContainerWithException(): void
    {
        $exception = new \Laminas\Stdlib\Exception\InvalidArgumentException('test');

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
            ->willThrowException($exception);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(Navigation::class, $container1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer();
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->setContainer($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setContainer($name);

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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
     * @throws ExceptionInterface
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
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getLiClass');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Menu::class, $page)
            ->willReturn($expected);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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
     * @throws ExceptionInterface
     */
    public function testHtmlifyWithException(): void
    {
        $exception = new \Laminas\I18n\Exception\RuntimeException('test');

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
        $page->expects(self::never())
            ->method('isActive');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::never())
            ->method('getLiClass');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Menu::class, $page)
            ->willThrowException($exception);

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertSame('', $helper->getIndent());

        $helper->setIndent(1);

        self::assertSame(' ', $helper->getIndent());

        $helper->setIndent('    ');

        self::assertSame('    ', $helper->getIndent());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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
        $parentPage->expects(self::never())
            ->method('getLabel');
        $parentPage->expects(self::never())
            ->method('getTextDomain');
        $parentPage->expects(self::never())
            ->method('getTitle');
        $parentPage->expects(self::never())
            ->method('getId');
        $parentPage->expects(self::never())
            ->method('getClass');
        $parentPage->expects(self::never())
            ->method('getHref');
        $parentPage->expects(self::never())
            ->method('getTarget');
        $parentPage->expects(self::never())
            ->method('hasPage');
        $parentPage->expects(self::never())
            ->method('hasPages');
        $parentPage->expects(self::never())
            ->method('getLiClass');

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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
        $parentPage->expects(self::never())
            ->method('getLabel');
        $parentPage->expects(self::never())
            ->method('getTextDomain');
        $parentPage->expects(self::never())
            ->method('getTitle');
        $parentPage->expects(self::never())
            ->method('getId');
        $parentPage->expects(self::never())
            ->method('getClass');
        $parentPage->expects(self::never())
            ->method('getHref');
        $parentPage->expects(self::never())
            ->method('getTarget');
        $parentPage->expects(self::never())
            ->method('hasPage');
        $parentPage->expects(self::never())
            ->method('hasPages');
        $parentPage->expects(self::never())
            ->method('getLiClass');

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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
     * @throws ExceptionInterface
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with(null)
            ->willReturn(null);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive(null, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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
        $parentPage->expects(self::never())
            ->method('getLabel');
        $parentPage->expects(self::never())
            ->method('getTextDomain');
        $parentPage->expects(self::never())
            ->method('getTitle');
        $parentPage->expects(self::never())
            ->method('getId');
        $parentPage->expects(self::never())
            ->method('getClass');
        $parentPage->expects(self::never())
            ->method('getHref');
        $parentPage->expects(self::never())
            ->method('getTarget');
        $parentPage->expects(self::never())
            ->method('hasPage');
        $parentPage->expects(self::never())
            ->method('hasPages');
        $parentPage->expects(self::never())
            ->method('getLiClass');

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, 2, 42));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

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

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testFindActiveException2(): void
    {
        $exception = new \Laminas\Stdlib\Exception\InvalidArgumentException('test');

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
        $serviceLocator->expects(self::never())
            ->method('build');

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException($exception);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setMinDepth(-1);
        $helper->setMaxDepth($maxDepth);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->findActive($name);
    }

    /** @throws Exception */
    public function testEscapeLabels(): void
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertTrue($helper->getEscapeLabels());

        $helper->escapeLabels(false);

        self::assertFalse($helper->getEscapeLabels());
    }

    /** @throws Exception */
    public function testSetAddClassToListItem(): void
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertFalse($helper->getAddClassToListItem());

        $helper->setAddClassToListItem(true);

        self::assertTrue($helper->getAddClassToListItem());
    }

    /** @throws Exception */
    public function testSetOnlyActiveBranch(): void
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertFalse($helper->getOnlyActiveBranch());

        $helper->setOnlyActiveBranch(true);

        self::assertTrue($helper->getOnlyActiveBranch());
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertNull($helper->getPartial());

        $helper->setPartial('test');

        self::assertSame('test', $helper->getPartial());

        $helper->setPartial(1);

        self::assertSame('test', $helper->getPartial());
    }

    /** @throws Exception */
    public function testSetRenderParents(): void
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertTrue($helper->getRenderParents());

        $helper->setRenderParents(false);

        self::assertFalse($helper->getRenderParents());
    }

    /** @throws Exception */
    public function testSetUlClass(): void
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertSame('navigation', $helper->getUlClass());

        $helper->setUlClass('test');

        self::assertSame('test', $helper->getUlClass());
    }

    /** @throws Exception */
    public function testSetLiClass(): void
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertSame('', $helper->getLiClass());

        $helper->setLiClass('test');

        self::assertSame('test', $helper->getLiClass());
    }

    /** @throws Exception */
    public function testSetLiActiveClass(): void
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        self::assertSame('active', $helper->getLiActiveClass());

        $helper->setLiActiveClass('test');

        self::assertSame('test', $helper->getLiActiveClass());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testDoNotRenderIfNoPageIsActive(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setContainer($container);

        self::assertSame('', $helper->render());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render menu: No partial view script provided');
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unable to render menu: A view partial supplied as an array must contain one value: the partial view script',
        );
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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

        $parentPage->addPage($page);

        $container = new Navigation();
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial  = 'testPartial';
        $expected = 'renderedPartial';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['container' => $container])
            ->willReturn($expected);

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

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
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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

        $container = new Navigation();
        $container->addPage($page);

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willReturn($container);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial  = 'testPartial';
        $expected = 'renderedPartial';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['container' => $container])
            ->willReturn($expected);

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

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
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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

        $parentPage->addPage($page);

        $container = new Navigation();
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial  = 'testPartial';
        $expected = 'renderedPartial';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['container' => $container])
            ->willReturn($expected);

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

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
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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
        $subPage->expects(self::never())
            ->method('getParent');
        $subPage->expects(self::never())
            ->method('isActive');
        $subPage->expects(self::never())
            ->method('getLabel');
        $subPage->expects(self::never())
            ->method('getTextDomain');
        $subPage->expects(self::never())
            ->method('getTitle');
        $subPage->expects(self::never())
            ->method('getId');
        $subPage->expects(self::never())
            ->method('getClass');
        $subPage->expects(self::never())
            ->method('getHref');
        $subPage->expects(self::never())
            ->method('getTarget');
        $subPage->expects(self::never())
            ->method('hasPage');
        $subPage->expects(self::never())
            ->method('hasPages');
        $subPage->expects(self::never())
            ->method('getLiClass');

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $expected = 'renderedPartial';
        $partial  = 'testPartial';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::once())
            ->method('render')
            ->with($partial, ['container' => $parentPage])
            ->willReturn($expected);

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

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
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
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
        $subPage->expects(self::never())
            ->method('getParent');
        $subPage->expects(self::never())
            ->method('isActive');
        $subPage->expects(self::never())
            ->method('getLabel');
        $subPage->expects(self::never())
            ->method('getTextDomain');
        $subPage->expects(self::never())
            ->method('getTitle');
        $subPage->expects(self::never())
            ->method('getId');
        $subPage->expects(self::never())
            ->method('getClass');
        $subPage->expects(self::never())
            ->method('getHref');
        $subPage->expects(self::never())
            ->method('getTarget');
        $subPage->expects(self::never())
            ->method('hasPage');
        $subPage->expects(self::never())
            ->method('hasPages');
        $subPage->expects(self::never())
            ->method('getLiClass');

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $matcher         = self::exactly(2);
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $expected = 'renderedPartial';
        $data     = ['container' => $parentPage];

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

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

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
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderPartialWithException(): void
    {
        $exception = new \Laminas\Stdlib\Exception\InvalidArgumentException('test');

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

        $parentPage->addPage($page);

        $container = new Navigation();
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->with($name)
            ->willThrowException($exception);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $partial = 'testPartial';

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setPartial($partial);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->renderPartial($name);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testDoNotRenderMenuIfNoPageIsActive(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setContainer($container);

        self::assertSame('', $helper->renderMenu());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenuNoActivePage(): void
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
        $page->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getLiClass');

        $container = new Navigation();
        $container->addPage($page);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn([]);

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(false);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        default => self::assertSame(AcceptHelperInterface::class, $name),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                    );

                    return match ($matcher->numberOfInvocations()) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '';
        $partial  = 'testPartial';

        $helper->setPartial($partial);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderMenu($name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu(): void
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
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(2))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
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
        $page->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::exactly(2))
            ->method('getLiClass')
            ->willReturn('li-class-active');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $pageParam, bool $recursive = true) use ($matcher, $parentPage, $page): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $pageParam),
                        default => self::assertSame($page, $pageParam),
                    };

                    self::assertTrue($recursive);

                    return true;
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
        $matcher = self::exactly(3);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        default => self::assertSame(AcceptHelperInterface::class, $name),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                    );

                    return match ($matcher->numberOfInvocations()) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

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
                    $parentPage,
                    $page,
                    $expected1,
                    $expected2,
                ): string {
                    self::assertSame(Menu::class, $prefix);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $pageParam),
                        default => self::assertSame($page, $pageParam),
                    };

                    self::assertTrue($escapeLabel);
                    self::assertFalse($addClassToListItem);
                    self::assertSame([], $attributes);
                    self::assertFalse($convertToButton);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $expected1,
                        default => $expected2,
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $matcher      = self::exactly(3);
        $escapePlugin->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('navigation', $value),
                        2 => self::assertSame('active', $value),
                        default => self::assertSame('active li-class-active', $value),
                    };

                    self::assertSame(0, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'navigation-escaped',
                        2 => 'active-escaped',
                        default => 'active-escaped2 li-class-active-escaped2',
                    };
                },
            );

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '<ul class="navigation-escaped">' . PHP_EOL . '    <li class="active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul>' . PHP_EOL . '            <li class="active-escaped2 li-class-active-escaped2">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame($expected, $helper->renderMenu($name));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu2(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(2))
            ->method('isActive')
            ->with(true)
            ->willReturn(true);
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
            ->method('getParent');
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::exactly(2))
            ->method('getLiClass')
            ->willReturn('li-class-active');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $pageParam, bool $recursive = true) use ($matcher, $parentPage, $page): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $pageParam),
                        default => self::assertSame($page, $pageParam),
                    };

                    self::assertTrue($recursive);

                    return true;
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
        $matcher = self::exactly(3);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        default => self::assertSame(AcceptHelperInterface::class, $name),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                    );

                    return match ($matcher->numberOfInvocations()) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

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
                    $parentPage,
                    $page,
                    $expected1,
                    $expected2,
                ): string {
                    self::assertSame(Menu::class, $prefix);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $pageParam),
                        default => self::assertSame($page, $pageParam),
                    };

                    self::assertTrue($escapeLabel);
                    self::assertFalse($addClassToListItem);
                    self::assertSame([], $attributes);
                    self::assertFalse($convertToButton);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $expected1,
                        default => $expected2,
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $matcher      = self::exactly(3);
        $escapePlugin->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher, $ulClass, $liActiveClass, $liClass): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($ulClass, $value),
                        2 => self::assertSame($liActiveClass . ' ' . $liClass, $value),
                        default => self::assertSame(
                            $liActiveClass . ' ' . $liClass . ' li-class-active',
                            $value,
                        ),
                    };

                    self::assertSame(0, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'ul-class-escaped',
                        2 => 'li-active-escaped li-class-escaped',
                        default => 'li-active-escaped2 li-class-escaped2 li-class-active-escaped2',
                    };
                },
            );

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '<ul class="ul-class-escaped">' . PHP_EOL . '    <li class="li-active-escaped li-class-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul>' . PHP_EOL . '            <li class="li-active-escaped2 li-class-escaped2 li-class-active-escaped2">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu3(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::exactly(2))
            ->method('isActive')
            ->with(true)
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
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::exactly(2))
            ->method('getLiClass')
            ->willReturn('li-class-active');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => null,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $pageParam, bool $recursive = true) use ($matcher, $parentPage, $page): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $pageParam),
                        default => self::assertSame($page, $pageParam),
                    };

                    self::assertTrue($recursive);

                    return true;
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
        $matcher = self::exactly(3);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        default => self::assertSame(AcceptHelperInterface::class, $name),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                    );

                    return match ($matcher->numberOfInvocations()) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

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
                    $parentPage,
                    $page,
                    $expected1,
                    $expected2,
                ): string {
                    self::assertSame(Menu::class, $prefix);

                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage, $pageParam),
                        default => self::assertSame($page, $pageParam),
                    };

                    self::assertTrue($escapeLabel);
                    self::assertFalse($addClassToListItem);
                    self::assertSame([], $attributes);
                    self::assertFalse($convertToButton);

                    return match ($matcher->numberOfInvocations()) {
                        1 => $expected1,
                        default => $expected2,
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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $matcher      = self::exactly(3);
        $escapePlugin->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher, $ulClass, $liClass): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($ulClass, $value),
                        2 => self::assertSame($liClass, $value),
                        default => self::assertSame($liClass . ' li-class-active', $value),
                    };

                    self::assertSame(0, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'ul-class-escaped',
                        2 => 'li-class-escaped',
                        default => 'li-class-escaped2 li-class-active-escaped2',
                    };
                },
            );

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '<ul class="ul-class-escaped">' . PHP_EOL . '    <li class="li-class-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '        <ul>' . PHP_EOL . '            <li class="li-class-escaped2 li-class-active-escaped2">' . PHP_EOL . '                <a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>' . PHP_EOL . '            </li>' . PHP_EOL . '        </ul>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu4(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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
            ->with(true)
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
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(false);
        $page->expects(self::exactly(2))
            ->method('getLiClass')
            ->willReturn('li-class-active');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
            ->willReturn(
                [
                    'page' => $page,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        default => self::assertSame(AcceptHelperInterface::class, $name),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                    );

                    return match ($matcher->numberOfInvocations()) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $expected1 = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>';

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Menu::class, $page)
            ->willReturn($expected1);

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $matcher      = self::exactly(2);
        $escapePlugin->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher, $ulClass, $liClass): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($ulClass, $value),
                        default => self::assertSame($liClass . ' li-class-active', $value),
                    };

                    self::assertSame(0, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'ul-class-escaped',
                        default => 'li-class-escaped li-class-active-escaped',
                    };
                },
            );

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '<ul class="ul-class-escaped">' . PHP_EOL . '    <li class="li-class-escaped li-class-active-escaped">' . PHP_EOL . '        <a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>' . PHP_EOL . '    </li>' . PHP_EOL . '</ul>';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => false],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu5(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
            ->willReturn([]);

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::never())
            ->method('accept');

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => false],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu6(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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
        $page->expects(self::once())
            ->method('hasPages')
            ->with(true)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getLiClass');

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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::never())
            ->method('accept');

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => false, 'minDepth' => 2],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu7(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
            ->willReturn([]);

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::never())
            ->method('accept');

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => false, 'minDepth' => -2],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu8(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, -1, null)
            ->willReturn([]);

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::never())
            ->method('accept');

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => false, 'minDepth' => null],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu9(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                ['depth' => 1],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::never())
            ->method('accept');

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => false, 'minDepth' => 2],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu10(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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
                    'page' => 42,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::never())
            ->method('accept');

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => false, 'minDepth' => 2],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu11(): void
    {
        $exception = new \Laminas\Stdlib\Exception\InvalidArgumentException('test');

        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::never())
            ->method('accept');

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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::once())
            ->method('parseContainer')
            ->willThrowException($exception);

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->renderMenu(
            $name,
            ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => false, 'minDepth' => 2],
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu12(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::once())
            ->method('isActive')
            ->with(true)
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
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getLiClass');

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 1, null)
            ->willReturn(
                ['depth' => 1],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page, true)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        default => self::assertSame(AcceptHelperInterface::class, $name),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                    );

                    return match ($matcher->numberOfInvocations()) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => true, 'minDepth' => 1],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu13(): void
    {
        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::once())
            ->method('isActive')
            ->with(true)
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
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::never())
            ->method('getLiClass');

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
                    'page' => 42,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page, true)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        default => self::assertSame(AcceptHelperInterface::class, $name),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                    );

                    return match ($matcher->numberOfInvocations()) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $escapePlugin->expects(self::never())
            ->method('__invoke');

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $expected = '';

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        self::assertSame(
            $expected,
            $helper->renderMenu(
                $name,
                ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => true, 'minDepth' => 1],
            ),
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu14(): void
    {
        $exception = new \Laminas\I18n\Exception\RuntimeException('test');

        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::once())
            ->method('isActive')
            ->with(true)
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
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::once())
            ->method('hasPages')
            ->with(false)
            ->willReturn(false);
        $page->expects(self::once())
            ->method('getLiClass')
            ->willReturn(null);

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
                    'page' => $parentPage,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page, true)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        default => self::assertSame(AcceptHelperInterface::class, $name),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                    );

                    return match ($matcher->numberOfInvocations()) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Menu::class, $page, true, false, [], false)
            ->willThrowException($exception);

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $matcher      = self::exactly(2);
        $escapePlugin->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher, $ulClass, $liClass): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($ulClass, $value),
                        default => self::assertSame($liClass, $value),
                    };

                    self::assertSame(0, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'ul-class-escaped',
                        default => 'li-class-escaped',
                    };
                },
            );

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->renderMenu(
            $name,
            ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => true, 'minDepth' => 1],
        );
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     */
    public function testRenderMenu15(): void
    {
        $exception = new \Laminas\I18n\Exception\RuntimeException('test');

        $name = 'Mezzio\Navigation\Top';

        $resource      = 'testResource';
        $privilege     = 'testPrivilege';
        $ulClass       = 'ul-class';
        $liClass       = 'li-class';
        $liActiveClass = 'li-active';

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
        $page->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $page->expects(self::never())
            ->method('getResource');
        $page->expects(self::never())
            ->method('getPrivilege');
        $page->expects(self::never())
            ->method('getParent');
        $page->expects(self::once())
            ->method('isActive')
            ->with(true)
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
        $page->expects(self::never())
            ->method('hasPage');
        $page->expects(self::never())
            ->method('hasPages');
        $page->expects(self::once())
            ->method('getLiClass')
            ->willReturn(null);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $parentPage,
                    'depth' => 1,
                ],
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page, true)
            ->willReturn(true);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        default => self::assertSame(AcceptHelperInterface::class, $name),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                    );

                    return match ($matcher->numberOfInvocations()) {
                        1 => $findActiveHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::once())
            ->method('toHtml')
            ->with(Menu::class, $page, true, false, [], false)
            ->willThrowException($exception);

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

        $escapePlugin = $this->createMock(EscapeHtmlAttr::class);
        $matcher      = self::exactly(2);
        $escapePlugin->expects($matcher)
            ->method('__invoke')
            ->willReturnCallback(
                static function (string $value, int $recurse = AbstractHelper::RECURSE_NONE) use ($matcher, $ulClass, $liClass): string {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($ulClass, $value),
                        default => self::assertSame($liClass, $value),
                    };

                    self::assertSame(0, $recurse);

                    return match ($matcher->numberOfInvocations()) {
                        1 => 'ul-class-escaped',
                        default => 'li-class-escaped',
                    };
                },
            );

        $renderer = $this->createMock(PartialRendererInterface::class);
        $renderer->expects(self::never())
            ->method('render');

        $helper = new Menu($serviceLocator, $htmlify, $containerParser, $escapePlugin, $renderer);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::never())
            ->method('getHelperPluginManager');

        assert($view instanceof PhpRenderer);
        $helper->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $helper->renderMenu(
            $name,
            ['ulClass' => $ulClass, 'liClass' => $liClass, 'liActiveClass' => $liActiveClass, 'onlyActiveBranch' => true, 'renderParents' => false, 'minDepth' => 1],
        );
    }
}
