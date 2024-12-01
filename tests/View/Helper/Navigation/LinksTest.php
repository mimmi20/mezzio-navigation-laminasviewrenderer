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

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\LinksInterface;
use Mimmi20\Mezzio\Navigation\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\Mezzio\Navigation\Page\Route;
use Mimmi20\Mezzio\Navigation\Page\Uri;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\FindFromProperty\FindFromPropertyInterface;
use Mimmi20\NavigationHelper\FindRoot\FindRootInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Constraint\IsInstanceOf;
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Links::setDefaultRole($defaultRole);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->findActive($name, $minDepth, $maxDepth));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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
     * @throws RuntimeException
     * @throws InvalidArgumentException
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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $helper->setMinDepth(-1);
        $helper->setMaxDepth($maxDepth);

        $expected = [];

        self::assertSame($expected, $helper->findActive($name));
    }

    /** @throws Exception */
    public function testSetRenderFlag(): void
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame(LinksInterface::RENDER_ALL, $helper->getRenderFlag());

        $helper->setRenderFlag(LinksInterface::RENDER_ALTERNATE);

        self::assertSame(LinksInterface::RENDER_ALTERNATE, $helper->getRenderFlag());
    }

    /** @throws Exception */
    public function testSearchRevSubsectionWithoutParent(): void
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn(null);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );
        self::assertNull($helper->searchRevSubsection($page));
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRevSubsectionWithParent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $parentPage = new Route();

        $page = $this->createMock(PageInterface::class);
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
                $page::class,
            ),
        );
        $parentPage->addPage($page);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($parentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRevSubsection($page));
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRevSubsectionWithDeepParent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $parentPage             = new Route();
        $parentParentPage       = new Route();
        $parentParentParentPage = new Route();

        $page = $this->createMock(PageInterface::class);
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
                $page::class,
            ),
        );
        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($parentParentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame($parentPage, $helper->searchRevSubsection($page));
    }

    /** @throws Exception */
    public function testSearchRevSectionWithoutParent(): void
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('getParent')
            ->willReturn(null);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );
        self::assertNull($helper->searchRevSection($page));
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRevSectionWithParent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $parentPage = new Route();

        $page = $this->createMock(PageInterface::class);
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
                $page::class,
            ),
        );
        $parentPage->addPage($page);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($parentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRevSection($page));
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRevSectionWithDeepParent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $parentPage       = new Route();
        $parentParentPage = new Route();

        $page = $this->createMock(PageInterface::class);
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
                $page::class,
            ),
        );
        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame($parentPage, $helper->searchRevSection($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testSearchRelSubsectionWithoutParent(): void
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('hasPages')
            ->willReturn(false);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );
        self::assertSame([], $helper->searchRelSubsection($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSubsectionWithParent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $parentPage = new Route();
        $page       = new Route();

        $parentPage->addPage($page);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );
        self::assertSame([], $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSubsectionWithDeepParent(): void
    {
        $page                   = new Route();
        $parentPage             = new Route();
        $parentParentPage       = new Route();
        $parentParentParentPage = new Route();

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);
        $parentParentParentPage->addPage($parentParentPage);

        $role = 'testRole';

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page], $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSubsectionWithDeepParent2(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        1 => false,
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
        $serviceLocator->expects(self::exactly(2))
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page2], $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSubsectionWithDeepParent3(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $page1, $page2, $page3): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page1, $page),
                        3 => self::assertSame($page3, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        1 => false,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page2, $page3], $helper->searchRelSubsection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testSearchRelSectionWithoutParent(): void
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $page = $this->createMock(PageInterface::class);
        $page->expects(self::once())
            ->method('hasPages')
            ->willReturn(false);

        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );
        self::assertSame([], $helper->searchRelSection($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSectionWithParent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $parentPage = new Route();
        $page       = new Route();

        $parentPage->addPage($page);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame([], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSectionWithDeepParent(): void
    {
        $page             = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSectionWithDeepParent2(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $pageParam, bool $recursive = true) use ($matcher, $page, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page2, $pageParam),
                        default => self::assertSame($page, $pageParam),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        1 => false,
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
        $serviceLocator->expects(self::exactly(2))
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSectionWithDeepParent3(): void
    {
        $page1            = new Route();
        $page2            = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        1 => false,
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
        $serviceLocator->expects(self::exactly(2))
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page2], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSectionWithDeepParent4(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $page1, $page2, $page3): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page1, $page),
                        3 => self::assertSame($page3, $page),
                        default => self::assertSame($page2, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        1 => false,
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page2, $page3], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelSectionWithDeepParent5(): void
    {
        $page             = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->searchRelSection($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelChapterWithoutParent(): void
    {
        $page = new Route();
        $role = null;
        $auth = null;

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page, 'rel', 'start')
            ->willReturn([]);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
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
                ],
            )
            ->willReturn($findFromPropertyHelper);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->with($page)
            ->willReturn($page);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertSame([], $helper->searchRelChapter($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelChapterWithParent(): void
    {
        $parentPage = new Route();
        $page       = new Route();

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page)
            ->willReturn(true);

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentPage, 'rel', 'start')
            ->willReturn([]);

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
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findFromPropertyHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindFromPropertyInterface::class, $name),
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
                        1 => $findFromPropertyHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $parentPage->addPage($page);

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$page], $helper->searchRelChapter($parentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelChapterWithDeepParent(): void
    {
        $page             = new Route();
        $parentPage       = new Route();
        $parentParentPage = new Route();

        $parentPage->addPage($page);
        $parentParentPage->addPage($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($parentPage)
            ->willReturn(true);

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentParentPage, 'rel', 'start')
            ->willReturn([]);

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
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findFromPropertyHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindFromPropertyInterface::class, $name),
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
                        1 => $findFromPropertyHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->with($parentParentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$parentPage], $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelChapterWithDeepParent2(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage2, $parentPage): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage2, $page),
                        default => self::assertSame($parentPage, $page),
                    };

                    self::assertTrue($recursive);

                    return match ($matcher->numberOfInvocations()) {
                        1 => false,
                        default => true,
                    };
                },
            );

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentParentPage, 'rel', 'start')
            ->willReturn([]);

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
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findFromPropertyHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindFromPropertyInterface::class, $name),
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
                        1 => $findFromPropertyHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->with($parentParentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$parentPage], $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelChapterWithDeepParent3(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage2, $parentPage): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage2, $page),
                        default => self::assertSame($parentPage, $page),
                    };

                    self::assertTrue($recursive);

                    return false;
                },
            );

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentParentPage, 'rel', 'start')
            ->willReturn([]);

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
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findFromPropertyHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindFromPropertyInterface::class, $name),
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
                        1 => $findFromPropertyHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->with($parentParentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([], $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelChapterWithDeepParent4(): void
    {
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

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(2);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $parentPage2, $parentPage): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($parentPage2, $page),
                        default => self::assertSame($parentPage, $page),
                    };

                    self::assertTrue($recursive);

                    return true;
                },
            );

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($parentParentPage, 'rel', 'start')
            ->willReturn([]);

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
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findFromPropertyHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindFromPropertyInterface::class, $name),
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
                        1 => $findFromPropertyHelper,
                        default => $acceptHelper,
                    };
                },
            );

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(2))
            ->method('find')
            ->with($parentParentPage)
            ->willReturn($parentParentPage);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame([$parentPage2, $parentPage], $helper->searchRelChapter($parentParentPage));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelPrevWithoutParent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $page = new Route();

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($page);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRelPrev($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelPrevWithParent(): void
    {
        $parentPage = new Route();
        $parentPage->setId('fgh');
        $page1 = new Route();
        $page1->setId('abc');
        $page2 = new Route();
        $page2->setId('xyz');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);

        $matcher = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $page1, $page2): bool {
                    match ($matcher->numberOfInvocations()) {
                        1, 2 => self::assertSame(
                            $page1,
                            $page,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        default => self::assertSame(
                            $page2,
                            $page,
                            (string) $matcher->numberOfInvocations(),
                        ),
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');

        $matcher = self::exactly(2);
        $rootFinder->expects($matcher)
            ->method('find')
            ->willReturnCallback(
                static function (PageInterface $page) use ($matcher, $page1, $page2, $parentPage): PageInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    return $parentPage;
                },
            );

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertNull($helper->searchRelPrev($page1));
        self::assertSame($page1, $helper->searchRelPrev($page2));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelPrevWithParent2(): void
    {
        $parentPage = new Route();
        $parentPage->setId('fgh');
        $page1 = new Route();
        $page1->setId('abc');
        $page2 = new Route();
        $page2->setId('xyz');
        $page2->setActive(false);
        $page2->setVisible(false);
        $page3 = new Route();
        $page3->setId('rst');

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);
        $parentPage->addPage($page3);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(7);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $page, bool $recursive = true) use ($matcher, $page1, $page2, $page3): bool {
                    match ($matcher->numberOfInvocations()) {
                        3, 6 => self::assertSame(
                            $page2,
                            $page,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        4, 7 => self::assertSame(
                            $page3,
                            $page,
                            (string) $matcher->numberOfInvocations(),
                        ),
                        default => self::assertSame(
                            $page1,
                            $page,
                            (string) $matcher->numberOfInvocations(),
                        ),
                    };

                    self::assertTrue($recursive, (string) $matcher->numberOfInvocations());

                    return match ($matcher->numberOfInvocations()) {
                        3, 6 => false,
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
        $serviceLocator->expects(self::exactly(7))
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $matcher = self::exactly(3);
        $rootFinder->expects($matcher)
            ->method('find')
            ->willReturnCallback(
                static function (PageInterface $page) use ($matcher, $page1, $page2, $page3, $parentPage): PageInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page1, $page),
                        3 => self::assertSame($page3, $page),
                        default => self::assertSame($page2, $page),
                    };

                    return $parentPage;
                },
            );

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertNull($helper->searchRelPrev($page1));
        self::assertNull($helper->searchRelPrev($page2));
        self::assertSame($page1, $helper->searchRelPrev($page3));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelNextWithoutParent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $page = new Route();

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($page);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRelNext($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelNextWithParent(): void
    {
        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::once())
            ->method('accept')
            ->with($page1)
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

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $matcher = self::exactly(2);
        $rootFinder->expects($matcher)
            ->method('find')
            ->willReturnCallback(
                static function (PageInterface $page) use ($matcher, $page1, $page2, $parentPage): PageInterface {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    return $parentPage;
                },
            );

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertNull($helper->searchRelNext($page2));
        self::assertSame($page2, $helper->searchRelNext($page1));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelStartWithoutParent(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $serviceLocator->expects(self::never())
            ->method('build');

        $page = new Route();

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::once())
            ->method('find')
            ->with($page)
            ->willReturn($page);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        self::assertNull($helper->searchRelStart($page));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelStartWithParent(): void
    {
        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::exactly(2))
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
        $serviceLocator->expects(self::exactly(2))
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $matcher = self::exactly(2);
        $rootFinder->expects($matcher)
            ->method('find')
            ->willReturnCallback(
                static function (PageInterface $page) use ($matcher, $page1, $page2, $parentPage): PageInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    return $parentPage;
                },
            );

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame($parentPage, $helper->searchRelStart($page1));
        self::assertSame($parentPage, $helper->searchRelStart($page2));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testSearchRelStartWithParent2(): void
    {
        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(2))
            ->method('rewind');
        $container->expects(self::exactly(2))
            ->method('current')
            ->willReturn($parentPage);

        $role = 'testRole';

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::exactly(2))
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
        $serviceLocator->expects(self::exactly(2))
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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $matcher = self::exactly(2);
        $rootFinder->expects($matcher)
            ->method('find')
            ->willReturnCallback(
                static function (PageInterface $page) use ($matcher, $page1, $page2, $container): ContainerInterface {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($page1, $page),
                        default => self::assertSame($page2, $page),
                    };

                    return $container;
                },
            );

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame($parentPage, $helper->searchRelStart($page1));
        self::assertSame($parentPage, $helper->searchRelStart($page2));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindRelationWithError(): void
    {
        $parentPage = new Route();
        $page1      = new Route();
        $page2      = new Route();

        $parentPage->addPage($page1);
        $parentPage->addPage($page2);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'foo';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            sprintf('Invalid argument: $rel must be "rel" or "rev"; "%s" given', $rel),
        );
        $this->expectExceptionCode(0);

        $helper->findRelation($page1, $rel, 'test');
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindNullRelationFromProperty(): void
    {
        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $page1 = $this->createMock(PageInterface::class);
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

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
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
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindPageRelationFromProperty(): void
    {
        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $page3 = $this->createMock(PageInterface::class);
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

        $page1 = $this->createMock(PageInterface::class);
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

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([$page3]);

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
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([$page3], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    #[Group('Render2')]
    public function testFindContainerRelationFromProperty(): void
    {
        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $container = new Navigation();

        $page1 = $this->createMock(PageInterface::class);
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

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([$parentPage]);

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
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([$parentPage], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindStringRelationFromProperty(): void
    {
        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $container = new Navigation();

        $page1 = $this->createMock(PageInterface::class);
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

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([$page3]);

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
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([$page3], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindStringRelationFromPropertyWithError(): void
    {
        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $container = new Navigation();

        $page1 = $this->createMock(PageInterface::class);
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

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
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
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindConfigRelationFromProperty(): void
    {
        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $page3 = $this->createMock(PageInterface::class);
        $page1 = $this->createMock(PageInterface::class);
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

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
            ->willReturn([$page3]);

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
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame([$page3], $helper->findRelation($page1, $rel, $type));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindConfigRelationFromPropertyWithError(): void
    {
        $parentPage = new Route();
        $page2      = new Route();
        $type       = 'test';

        $page1 = $this->createMock(PageInterface::class);
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

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $findFromPropertyHelper->expects(self::once())
            ->method('find')
            ->with($page1, 'rel', 'test')
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
                FindFromPropertyInterface::class,
                [
                    'authorization' => $auth,
                    'renderInvisible' => false,
                    'role' => $role,
                ],
            )
            ->willReturn($findFromPropertyHelper);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'foo';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            sprintf('Invalid relation attribute "%s", must be "rel" or "rev"', $rel),
        );
        $this->expectExceptionCode(0);

        $helper->renderLink($page, $rel, 'test');
    }

    /**
     * @throws Exception
     * @throws DomainException
     */
    public function testRenderLinkWithoutHref(): void
    {
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
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn('');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('get');

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

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $rel = 'rel';

        self::assertSame('', $helper->renderLink($page, $rel, 'test'));
    }

    /**
     * @throws Exception
     * @throws DomainException
     */
    public function testRenderLinkWithHref(): void
    {
        $href  = '/test.html';
        $label = 'test-label';

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
        $matcher = self::exactly(5);
        $page->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $param) use ($matcher): string | null {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame('type', $param),
                        2 => self::assertSame('hreflang', $param),
                        3 => self::assertSame('charset', $param),
                        4 => self::assertSame('lang', $param),
                        default => self::assertSame('media', $param),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1, 5 => null,
                        2, 4 => 'de',
                        default => throw new \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException(
                            'fail',
                        ),
                    };
                },
            );

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
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
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

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::once())
            ->method('itemToString')
            ->with($params)
            ->willReturn($expected);

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setRole($role);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        self::assertSame($expected, $helper->renderLink($page, $attrib, $relation));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testDoNotRenderIfNoPageIsActive(): void
    {
        $maxDepth = null;
        $minDepth = 0;

        $container = $this->createMock(ContainerInterface::class);

        $findActiveHelper = $this->createMock(FindActiveInterface::class);
        $findActiveHelper->expects(self::once())
            ->method('find')
            ->with($container, $minDepth, $maxDepth)
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::never())
            ->method('itemToString');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        $helper->setContainer($container);

        self::assertSame('', $helper->render());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testRender(): void
    {
        $name      = 'Mezzio\Navigation\Top';
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

        $page = $this->createMock(PageInterface::class);
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

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

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

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $matcher                = self::exactly(31);
        $findFromPropertyHelper->expects($matcher)
            ->method('find')
            ->willReturnCallback(
                static function (PageInterface $pageParam, string $rel, string $type) use ($matcher, $page): array {
                    self::assertSame($page, $pageParam);

                    match ($matcher->numberOfInvocations()) {
                        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16 => self::assertSame(
                            'rel',
                            $rel,
                        ),
                        default => self::assertSame('rev', $rel),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1, 17 => self::assertSame('alternate', $type),
                        2, 18 => self::assertSame('stylesheet', $type),
                        3, 11, 19 => self::assertSame('start', $type),
                        4, 20 => self::assertSame('next', $type),
                        5, 21 => self::assertSame('prev', $type),
                        6, 22 => self::assertSame('contents', $type),
                        7, 23 => self::assertSame('index', $type),
                        8, 24 => self::assertSame('glossary', $type),
                        9, 25 => self::assertSame('copyright', $type),
                        10, 26 => self::assertSame('chapter', $type),
                        12, 27 => self::assertSame('section', $type),
                        13, 28 => self::assertSame('subsection', $type),
                        14, 29 => self::assertSame('appendix', $type),
                        15, 30 => self::assertSame('help', $type),
                        default => self::assertSame('bookmark', $type),
                    };

                    return [];
                },
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $matcher      = self::exactly(3);
        $acceptHelper->expects($matcher)
            ->method('accept')
            ->willReturnCallback(
                static function (PageInterface $pageParam, bool $recursive = true) use ($matcher, $parentPage, $page): bool {
                    match ($matcher->numberOfInvocations()) {
                        2 => self::assertSame($page, $pageParam),
                        default => self::assertSame($parentPage, $pageParam),
                    };

                    self::assertTrue($recursive);

                    return true;
                },
            );

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(35);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $findFromPropertyHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        5, 8, 15 => self::assertSame(AcceptHelperInterface::class, $name),
                        default => self::assertSame(FindFromPropertyInterface::class, $name),
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
                        5,8,15 => $acceptHelper,
                        default => $findFromPropertyHelper,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $matcher    = self::exactly(2);
        $rootFinder->expects($matcher)
            ->method('setRoot')
            ->willReturnCallback(
                static function (ContainerInterface | null $root) use ($matcher, $container): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($container, $root),
                        default => self::assertNull($root),
                    };
                },
            );
        $rootFinder->expects(self::exactly(7))
            ->method('find')
            ->with($page)
            ->willReturn($parentPage);

        $expected = sprintf('<link rel="start" href="%s" title="%s" />', $parentUri, $parentLabel);

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::once())
            ->method('itemToString')
            ->with((object) ['rel' => 'start', 'href' => $parentUri, 'title' => $parentLabel])
            ->willReturn($expected);

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        self::assertSame($expected, $helper->render($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testRender2(): void
    {
        $name      = 'Mezzio\Navigation\Top';
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

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

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

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $matcher = self::exactly(31);
        $findFromPropertyHelper->expects($matcher)
            ->method('find')
            ->willReturnCallback(
                static function (PageInterface $pageParam, string $rel, string $type) use ($matcher, $parentPage): array {
                    self::assertSame($parentPage, $pageParam);

                    match ($matcher->numberOfInvocations()) {
                        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16 => self::assertSame(
                            'rel',
                            $rel,
                        ),
                        default => self::assertSame('rev', $rel),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1, 17 => self::assertSame('alternate', $type),
                        2, 18 => self::assertSame('stylesheet', $type),
                        3, 11, 19 => self::assertSame('start', $type),
                        4, 20 => self::assertSame('next', $type),
                        5, 21 => self::assertSame('prev', $type),
                        6, 22 => self::assertSame('contents', $type),
                        7, 23 => self::assertSame('index', $type),
                        8, 24 => self::assertSame('glossary', $type),
                        9, 25 => self::assertSame('copyright', $type),
                        10, 26 => self::assertSame('chapter', $type),
                        12, 27 => self::assertSame('section', $type),
                        13, 28 => self::assertSame('subsection', $type),
                        14, 29 => self::assertSame('appendix', $type),
                        15, 30 => self::assertSame('help', $type),
                        default => self::assertSame('bookmark', $type),
                    };

                    return [];
                },
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->with($page)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(34);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $findFromPropertyHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        7, 14 => self::assertSame(AcceptHelperInterface::class, $name),
                        default => self::assertSame(FindFromPropertyInterface::class, $name),
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
                        7, 14 => $acceptHelper,
                        default => $findFromPropertyHelper,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $matcher    = self::exactly(2);
        $rootFinder->expects($matcher)
            ->method('setRoot')
            ->willReturnCallback(
                static function (ContainerInterface | null $root) use ($matcher, $container): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($container, $root),
                        default => self::assertNull($root),
                    };
                },
            );
        $rootFinder->expects(self::exactly(7))
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentPage);

        $expected = '';

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::never())
            ->method('itemToString');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        self::assertSame($expected, $helper->render($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testRenderWithException(): void
    {
        $exception = new \Laminas\Stdlib\Exception\InvalidArgumentException('test');

        $name      = 'Mezzio\Navigation\Top';
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
            ->method('get');

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
            ->willThrowException($exception);

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::never())
            ->method('itemToString');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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

        $helper->render($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testRender3(): void
    {
        $name      = 'Mezzio\Navigation\Top';
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
        $parentPage->setRel(['next' => '#abc', 'prev' => '#def', 4711 => '#xyz']);
        $parentPage->setRev(['next' => '#fgh', 'prev' => '#ijk', 42 => '#stu']);

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
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($uri);
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::never())
            ->method('get');
        $page->expects(self::never())
            ->method('getDefinedRel');
        $page->expects(self::never())
            ->method('getDefinedRev');

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
            ->with($container, 0, null)
            ->willReturn(
                [
                    'page' => $parentPage,
                    'depth' => 1,
                ],
            );

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $matcher = self::exactly(31);
        $findFromPropertyHelper->expects($matcher)
            ->method('find')
            ->willReturnCallback(
                static function (PageInterface $pageParam, string $rel, string $type) use ($matcher, $parentPage): array {
                    self::assertSame($parentPage, $pageParam);

                    match ($matcher->numberOfInvocations()) {
                        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16 => self::assertSame(
                            'rel',
                            $rel,
                        ),
                        default => self::assertSame('rev', $rel),
                    };

                    match ($matcher->numberOfInvocations()) {
                        1, 17 => self::assertSame('alternate', $type),
                        2, 18 => self::assertSame('stylesheet', $type),
                        3, 11, 19 => self::assertSame('start', $type),
                        4, 20 => self::assertSame('next', $type),
                        5, 21 => self::assertSame('prev', $type),
                        6, 22 => self::assertSame('contents', $type),
                        7, 23 => self::assertSame('index', $type),
                        8, 24 => self::assertSame('glossary', $type),
                        9, 25 => self::assertSame('copyright', $type),
                        10, 26 => self::assertSame('chapter', $type),
                        12, 27 => self::assertSame('section', $type),
                        13, 28 => self::assertSame('subsection', $type),
                        14, 29 => self::assertSame('appendix', $type),
                        15, 30 => self::assertSame('help', $type),
                        default => self::assertSame('bookmark', $type),
                    };

                    return [];
                },
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->with($page)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(34);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findActiveHelper, $findFromPropertyHelper, $acceptHelper): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(FindActiveInterface::class, $name),
                        7, 14 => self::assertSame(AcceptHelperInterface::class, $name),
                        default => self::assertSame(FindFromPropertyInterface::class, $name),
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
                        7, 14 => $acceptHelper,
                        default => $findFromPropertyHelper,
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

        $rootFinder = $this->createMock(FindRootInterface::class);
        $matcher    = self::exactly(2);
        $rootFinder->expects($matcher)
            ->method('setRoot')
            ->willReturnCallback(
                static function (ContainerInterface | null $root) use ($matcher, $container): void {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($container, $root),
                        default => self::assertNull($root),
                    };
                },
            );
        $rootFinder->expects(self::exactly(7))
            ->method('find')
            ->with($parentPage)
            ->willReturn($parentPage);

        $expected = '';

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::never())
            ->method('itemToString');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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
        $helper->setRenderFlag(LinksInterface::RENDER_ALL);

        self::assertSame($expected, $helper->render($name));
    }

    /** @throws Exception */
    public function testToStringExceptionInRenderer(): void
    {
        $auth      = $this->createMock(AuthorizationInterface::class);
        $exception = new InvalidArgumentException('test');

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
            ->with(null)
            ->willThrowException($exception);

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::never())
            ->method('find');

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::never())
            ->method('itemToString');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

        assert($auth instanceof AuthorizationInterface);
        $helper->setAuthorization($auth);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        self::assertSame('', (string) $helper);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     */
    public function testFindAllRelations(): void
    {
        $name      = 'Mezzio\Navigation\Top';
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
        $parentPage->setRel(['next' => '#abc', 'prev' => '#def', 4711 => '#xyz']);
        $parentPage->setRev(['next' => '#fgh', 'prev' => '#ijk', 42 => '#stu']);

        $page = $this->createMock(PageInterface::class);
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
        $page->expects(self::once())
            ->method('getDefinedRel')
            ->willReturn(['prev', 'next']);
        $page->expects(self::once())
            ->method('getDefinedRev')
            ->willReturn(['index']);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $role = 'testRole';

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects(self::never())
            ->method('isGranted');

        $findFromPropertyHelper = $this->createMock(FindFromPropertyInterface::class);
        $matcher = self::exactly(31);
        $findFromPropertyHelper->expects($matcher)
            ->method('find')
            ->willReturnCallback(
                static function (PageInterface $pageParam, string $rel, string $type) use ($matcher, $page): array {
                    $invocation = $matcher->numberOfInvocations();

                    self::assertSame($page, $pageParam, (string) $invocation);

                    match ($invocation) {
                        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16 => self::assertSame(
                            'rel',
                            $rel,
                            (string) $invocation,
                        ),
                        default => self::assertSame('rev', $rel, (string) $invocation),
                    };

                    match ($invocation) {
                        1, 17 => self::assertSame('alternate', $type, (string) $invocation),
                        2, 18 => self::assertSame('stylesheet', $type, (string) $invocation),
                        3, 11, 19 => self::assertSame('start', $type, (string) $invocation),
                        4, 20 => self::assertSame('next', $type, (string) $invocation),
                        5, 21 => self::assertSame('prev', $type, (string) $invocation),
                        6, 22 => self::assertSame('contents', $type, (string) $invocation),
                        7, 23 => self::assertSame('index', $type, (string) $invocation),
                        8, 24 => self::assertSame('glossary', $type, (string) $invocation),
                        9, 25 => self::assertSame('copyright', $type, (string) $invocation),
                        10, 26 => self::assertSame('chapter', $type, (string) $invocation),
                        12, 27 => self::assertSame('section', $type, (string) $invocation),
                        13, 28 => self::assertSame('subsection', $type, (string) $invocation),
                        14, 29 => self::assertSame('appendix', $type, (string) $invocation),
                        15, 30 => self::assertSame('help', $type, (string) $invocation),
                        default => self::assertSame('bookmark', $type, (string) $invocation),
                    };

                    return match ($invocation) {
                        4 => ['#abc'],
                        5 => ['#def'],
                        default => [],
                    };
                },
            );

        $acceptHelper = $this->createMock(AcceptHelperInterface::class);
        $acceptHelper->expects(self::exactly(2))
            ->method('accept')
            ->with($parentPage, true)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');
        $matcher = self::exactly(33);
        $serviceLocator->expects($matcher)
            ->method('build')
            ->willReturnCallback(
                static function (string $name, array | null $options = null) use ($matcher, $auth, $role, $findFromPropertyHelper, $acceptHelper): mixed {
                    $invocation = $matcher->numberOfInvocations();

                    match ($invocation) {
                        4, 13 => self::assertSame(\Mimmi20\NavigationHelper\Accept\AcceptHelperInterface::class, $name, (string) $invocation),
                        default => self::assertSame(FindFromPropertyInterface::class, $name, (string) $invocation),
                    };

                    self::assertSame(
                        [
                            'authorization' => $auth,
                            'renderInvisible' => false,
                            'role' => $role,
                        ],
                        $options,
                        (string) $invocation,
                    );

                    return match ($invocation) {
                        4, 13 => $acceptHelper,
                        default => $findFromPropertyHelper,
                    };
                },
            );

        $htmlify = $this->createMock(HtmlifyInterface::class);
        $htmlify->expects(self::never())
            ->method('toHtml');

        $containerParser = $this->createMock(ContainerParserInterface::class);
        $containerParser->expects(self::never())
            ->method('parseContainer');

        $rootFinder = $this->createMock(FindRootInterface::class);
        $rootFinder->expects(self::never())
            ->method('setRoot');
        $rootFinder->expects(self::exactly(5))
            ->method('find')
            ->with($page)
            ->willReturn($parentPage);

        $expected = [
            'rel' => [
                'start' => [$parentPage],
                'next' => ['#abc'],
                'prev' => ['#def'],
            ],
            'rev' => [],
        ];

        $headLink = $this->createMock(HeadLink::class);
        $headLink->expects(self::never())
            ->method('__invoke');
        $headLink->expects(self::never())
            ->method('itemToString');

        $helper = new Links($serviceLocator, $htmlify, $containerParser, $rootFinder, $headLink);

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
        $helper->setRenderFlag(LinksInterface::RENDER_ALL);

        self::assertSame($expected, $helper->findAllRelations($page));
    }
}
