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
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\I18n\View\Helper\Translate;
use Laminas\Log\Logger;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Helper\Partial;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mezzio\Navigation\Navigation;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Uri;
use PHPUnit\Framework\TestCase;

final class BreadcrumbsTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMinDepth(): void
    {
        $minDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertSame(1, $helper->getMinDepth());

        $helper->setMinDepth($minDepth);

        self::assertSame($minDepth, $helper->getMinDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRenderInvisible(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Breadcrumbs::setDefaultRole($defaultRole);

        self::assertSame($defaultRole, $helper->getRole());
        self::assertTrue($helper->hasRole());

        $helper->setRole($role);

        self::assertSame($role, $helper->getRole());
        self::assertTrue($helper->hasRole());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetUseAuthorization(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        /* @var AuthorizationInterface $defaultAuth */
        Breadcrumbs::setDefaultAuthorization($defaultAuth);

        self::assertSame($defaultAuth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($auth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetTranslator(): void
    {
        $translator     = $this->createMock(TranslatorInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);
        $textDomain     = 'test';

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertTrue($helper->isTranslatorEnabled());
        self::assertNull($helper->getTranslator());
        self::assertFalse($helper->hasTranslator());
        self::assertSame('default', $helper->getTranslatorTextDomain());

        /* @var TranslatorInterface $translator */
        $helper->setTranslator($translator);
        $helper->setTranslatorTextDomain($textDomain);

        self::assertSame($translator, $helper->getTranslator());
        self::assertSame($textDomain, $helper->getTranslatorTextDomain());
        self::assertTrue($helper->hasTranslator());

        $helper->setTranslatorEnabled(false);

        self::assertNull($helper->getTranslator());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetView(): void
    {
        $view           = $this->createMock(RendererInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertNull($helper->getView());

        /* @var RendererInterface $view */
        $helper->setView($view);

        self::assertSame($view, $helper->getView());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithNumber(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Container must be a string alias or an instance of Mezzio\Navigation\ContainerInterface');

        $helper->setContainer(1);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithStringDefaultNotFound(): void
    {
        $logger = $this->createMock(Logger::class);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(Navigation::class)
            ->willThrowException(new ServiceNotFoundException('test'));

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', Navigation::class));

        $helper->setContainer('default');
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
    public function testSetContainerWithStringDefaultFound(): void
    {
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(Navigation::class)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $helper->setContainer('default');

        self::assertSame($container, $helper->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetContainerWithStringNavigationNotFound(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', $name));

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
    public function testSetContainerWithStringNavigationFound(): void
    {
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $helper->setContainer($name);

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
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Navigation::class], ['navigation'])
            ->willReturnOnConsecutiveCalls(false, false);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', $name));

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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $helper->setContainer($name);

        $label                  = 'testLabel';
        $tranalatedLabel        = 'testLabelTranslated';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $tranalatedTitle        = 'testTitleTranslated';
        $textDomain             = 'testDomain';
        $id                     = 'testId';
        $class                  = 'test-class';
        $href                   = '#';
        $target                 = '_blank';

        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects(self::never())
            ->method('translate');

        /* @var TranslatorInterface $translator */
        $helper->setTranslator($translator);

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$label, $textDomain], [$title, $textDomain])
            ->willReturnOnConsecutiveCalls($tranalatedLabel, $tranalatedTitle);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(6))
            ->method('__invoke')
            ->withConsecutive(
                [$tranalatedLabel],
                ['id'],
                ['title'],
                ['class'],
                ['href'],
                ['target']
            )
            ->willReturnOnConsecutiveCalls(
                $escapedTranalatedLabel,
                'idEscaped',
                'titleEscaped',
                'classEscaped',
                'hrefEscaped',
                'targetEscaped'
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(
                [$id],
                [$tranalatedTitle],
                [$class],
                [$href],
                [$target]
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                'testClassEscaped',
                '#Escaped',
                '_blankEscaped'
            );

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::once())
            ->method('has')
            ->with('translate')
            ->willReturn(true);
        $viewPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['translate'], ['escapeHtml'])
            ->willReturnOnConsecutiveCalls($translatePlugin, $escapeHtml);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::exactly(2))
            ->method('plugin')
            ->withConsecutive(['escapehtml'], ['escapehtmlattr'])
            ->willReturnOnConsecutiveCalls($escapeHtml, $escapeHtmlAttr);
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

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
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($title);
        $page->expects(self::exactly(2))
            ->method('getTextDomain')
            ->willReturn($textDomain);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($id);
        $page->expects(self::once())
            ->method('getClass')
            ->willReturn($class);
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($href);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($target);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->htmlify($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlifyWithoutTranslator(): void
    {
        $expected  = '<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $logger    = $this->createMock(Logger::class);
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $helper->setContainer($name);

        $label                  = 'testLabel';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $id                     = 'testId';
        $class                  = 'test-class';
        $href                   = '#';
        $target                 = null;

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(
                [$label],
                ['id'],
                ['title'],
                ['class'],
                ['href']
            )
            ->willReturnOnConsecutiveCalls(
                $escapedTranalatedLabel,
                'idEscaped',
                'titleEscaped',
                'classEscaped',
                'hrefEscaped'
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(
                [$id],
                [$title],
                [$class],
                [$href]
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                'testClassEscaped',
                '#Escaped'
            );

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::once())
            ->method('has')
            ->with('translate')
            ->willReturn(false);
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('escapeHtml')
            ->willReturn($escapeHtml);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::exactly(2))
            ->method('plugin')
            ->withConsecutive(['escapehtml'], ['escapehtmlattr'])
            ->willReturnOnConsecutiveCalls($escapeHtml, $escapeHtmlAttr);
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

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
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($title);
        $page->expects(self::never())
            ->method('getTextDomain');
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($id);
        $page->expects(self::once())
            ->method('getClass')
            ->willReturn($class);
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($href);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($target);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->htmlify($page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetIndent(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
            ->willReturn(false);

        $container = new Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
        $name   = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
    public function testFindActiveNoActivePageWithoutDepth(): void
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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetPartial(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertNull($helper->getPartial());

        $helper->setPartial('test');

        self::assertSame('test', $helper->getPartial());

        $helper->setPartial(1);

        self::assertSame('test', $helper->getPartial());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetLinkLast(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertFalse($helper->getLinkLast());

        $helper->setLinkLast(true);

        self::assertTrue($helper->getLinkLast());

        $helper->setLinkLast(false);

        self::assertFalse($helper->getLinkLast());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetSeparator(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertSame(' &gt; ', $helper->getSeparator());

        $helper->setSeparator('/');

        self::assertSame('/', $helper->getSeparator());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithParamsWithoutPartial(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        /* @var PhpRenderer $view */
        $helper->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: No partial view script provided');

        $helper->renderPartialWithParams(['abc' => 'test'], $name);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithParamsWithWrongPartial(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        /* @var PhpRenderer $view */
        $helper->setView($view);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: A view partial supplied as an array must contain one value: the partial view script');

        $helper->renderPartialWithParams(['abc' => 'test'], $name);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithParams(): void
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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['abc' => 'test', 'pages' => [$parentPage, $page], 'separator' => $seperator])
            ->willReturn($expected);

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], $name));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithParamsAndArrayPartial(): void
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($container);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [$parentPage, $page], 'separator' => $seperator, 'abc' => 'test'])
            ->willReturn($expected);

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], null, [$partial, 'test']));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithParamsAndArrayPartialRenderingPage(): void
    {
        $logger = $this->createMock(Logger::class);

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

        $subPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $subPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $subPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $subPage->expects(self::exactly(2))
            ->method('getParent')
            ->willReturn($parentPage);
        $subPage->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        /* @var PageInterface $subPage */
        $page->addPage($subPage);
        $parentPage->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [$parentPage, $subPage], 'separator' => $seperator, 'abc' => 'test'])
            ->willReturn($expected);

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], null, [$partial, 'test']));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithParamsRenderError(): void
    {
        $logger = $this->createMock(Logger::class);

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

        $subPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $subPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $subPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $subPage->expects(self::exactly(2))
            ->method('getParent')
            ->willReturn($parentPage);
        $subPage->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        /* @var PageInterface $subPage */
        $page->addPage($subPage);
        $parentPage->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [$parentPage, $subPage], 'separator' => $seperator, 'abc' => 'test'])
            ->willReturnSelf();

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: A view partial was not rendered correctly');

        $helper->renderPartialWithParams(['abc' => 'test'], null, [$partial, 'test']);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithParamsNoActivePage(): void
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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [], 'separator' => $seperator, 'abc' => 'test'])
            ->willReturn($expected);

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartialWithParams(['abc' => 'test'], $name));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithoutPartial(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        /* @var PhpRenderer $view */
        $helper->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: No partial view script provided');

        $helper->renderPartial($name);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithWrongPartial(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $helper->setSeparator('/');
        $helper->setLinkLast(true);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        /* @var PhpRenderer $view */
        $helper->setView($view);

        $helper->setPartial(['a', 'b', 'c']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: A view partial supplied as an array must contain one value: the partial view script');

        $helper->renderPartial($name);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartial(): void
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
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [$parentPage, $page], 'separator' => $seperator])
            ->willReturn($expected);

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialNoActivePage(): void
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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [], 'separator' => $seperator])
            ->willReturn($expected);

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial($name));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithArrayPartial(): void
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

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($container);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [$parentPage, $page], 'separator' => $seperator])
            ->willReturn($expected);

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialWithArrayPartialRenderingPage(): void
    {
        $logger = $this->createMock(Logger::class);

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

        $subPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $subPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $subPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $subPage->expects(self::exactly(2))
            ->method('getParent')
            ->willReturn($parentPage);
        $subPage->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        /* @var PageInterface $subPage */
        $page->addPage($subPage);
        $parentPage->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [$parentPage, $subPage], 'separator' => $seperator])
            ->willReturn($expected);

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderPartial(null, [$partial, 'test']));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderPartialRenderError(): void
    {
        $logger = $this->createMock(Logger::class);

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

        $subPage = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subPage->expects(self::once())
            ->method('isVisible')
            ->with(false)
            ->willReturn(true);
        $subPage->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);
        $subPage->expects(self::once())
            ->method('getPrivilege')
            ->willReturn($privilege);
        $subPage->expects(self::exactly(2))
            ->method('getParent')
            ->willReturn($parentPage);
        $subPage->expects(self::once())
            ->method('isActive')
            ->with(false)
            ->willReturn(true);

        /* @var PageInterface $subPage */
        $page->addPage($subPage);
        $parentPage->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setContainer($parentPage);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [$parentPage, $subPage], 'separator' => $seperator])
            ->willReturnSelf();

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to render breadcrumbs: A view partial was not rendered correctly');

        $helper->renderPartial(null, [$partial, 'test']);
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
    public function testRenderStraightNoActivePage(): void
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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected  = '';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight($name));
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
    public function testRenderStraight(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource               = 'testResource';
        $privilege              = 'testPrivilege';
        $label                  = 'testLabel';
        $tranalatedLabel        = 'testLabelTranslated';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $tranalatedTitle        = 'testTitleTranslated';
        $textDomain             = 'testDomain';
        $id                     = 'testId';
        $class                  = 'test-class';
        $href                   = '#';
        $target                 = null;

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
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($label);
        $page->expects(self::exactly(2))
            ->method('getTextDomain')
            ->willReturn($textDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($title);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($id);
        $page->expects(self::once())
            ->method('getClass')
            ->willReturn($class);
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($href);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($target);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>/<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(11))
            ->method('__invoke')
            ->withConsecutive(
                [$tranalatedLabel],
                ['id'],
                ['title'],
                ['class'],
                ['href'],
                ['translated-parent-label'],
                ['id'],
                ['title'],
                ['class'],
                ['href'],
                ['target']
            )
            ->willReturnOnConsecutiveCalls(
                $escapedTranalatedLabel,
                'idEscaped',
                'titleEscaped',
                'classEscaped',
                'hrefEscaped',
                'parent-label-escaped',
                'parent-id-escaped',
                'parent-title-escaped',
                'parent-class-escaped',
                'parent-href-escaped',
                'parent-target-escaped'
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(9))
            ->method('__invoke')
            ->withConsecutive(
                [$id],
                [$tranalatedTitle],
                [$class],
                [$href],
                ['parent-id'],
                ['translated-parent-title'],
                ['parent-class'],
                ['##'],
                ['self']
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                'testClassEscaped',
                '#Escaped',
                'parent-id-escaped',
                'parent-title-escaped',
                'parent-class-escaped',
                '##-escaped',
                'self-escaped'
            );

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive([$label, $textDomain], [$title, $textDomain], ['parent-label', 'parent-text-domain'], ['parent-title', 'parent-text-domain'])
            ->willReturnOnConsecutiveCalls($tranalatedLabel, $tranalatedTitle, 'translated-parent-label', 'translated-parent-title');

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::exactly(2))
            ->method('has')
            ->with('translate')
            ->willReturn(true);
        $viewPluginManager->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(['translate'], ['escapeHtml'], ['translate'], ['escapeHtml'])
            ->willReturnOnConsecutiveCalls($translatePlugin, $escapeHtml, $translatePlugin, $escapeHtml);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::exactly(4))
            ->method('plugin')
            ->withConsecutive(['escapehtml'], ['escapehtmlattr'], ['escapehtml'], ['escapehtmlattr'])
            ->willReturnOnConsecutiveCalls($escapeHtml, $escapeHtmlAttr, $escapeHtml, $escapeHtmlAttr);
        $view->expects(self::exactly(2))
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight($name));
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
    public function testRenderStraightWithoutLinkAtEnd(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource               = 'testResource';
        $privilege              = 'testPrivilege';
        $label                  = 'testLabel';
        $tranalatedLabel        = 'testLabelTranslated';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $tranalatedTitle        = 'testTitleTranslated';
        $textDomain             = 'testDomain';
        $id                     = 'testId';
        $class                  = 'test-class';
        $href                   = '#';
        $target                 = null;

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

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $role = 'testRole';

        $helper->setRole($role);
        $helper->setContainer($container);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::exactly(2))
            ->method('isGranted')
            ->with($role, $resource, $privilege)
            ->willReturn(true);

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected  = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>/testLabelTranslatedAndEscaped';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(false);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(7))
            ->method('__invoke')
            ->withConsecutive(
                [$tranalatedLabel],
                ['translated-parent-label'],
                ['id'],
                ['title'],
                ['class'],
                ['href'],
                ['target']
            )
            ->willReturnOnConsecutiveCalls(
                $escapedTranalatedLabel,
                'parent-label-escaped',
                'parent-id-escaped',
                'parent-title-escaped',
                'parent-class-escaped',
                'parent-href-escaped',
                'parent-target-escaped'
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(
                ['parent-id'],
                ['translated-parent-title'],
                ['parent-class'],
                ['##'],
                ['self']
            )
            ->willReturnOnConsecutiveCalls(
                'parent-id-escaped',
                'parent-title-escaped',
                'parent-class-escaped',
                '##-escaped',
                'self-escaped'
            );

        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects(self::never())
            ->method('translate');

        /* @var TranslatorInterface $translator */
        $helper->setTranslator($translator);

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::exactly(3))
            ->method('__invoke')
            ->withConsecutive([$label, $textDomain], ['parent-label', 'parent-text-domain'], ['parent-title', 'parent-text-domain'])
            ->willReturnOnConsecutiveCalls($tranalatedLabel, 'translated-parent-label', 'translated-parent-title');

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::exactly(2))
            ->method('has')
            ->with('translate')
            ->willReturn(true);
        $viewPluginManager->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(['translate'], ['escapeHtml'], ['translate'], ['escapeHtml'])
            ->willReturnOnConsecutiveCalls($translatePlugin, $escapeHtml, $translatePlugin, $escapeHtml);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::exactly(2))
            ->method('plugin')
            ->withConsecutive(['escapehtml'], ['escapehtmlattr'])
            ->willReturnOnConsecutiveCalls($escapeHtml, $escapeHtmlAttr);
        $view->expects(self::exactly(2))
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->renderStraight());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderWithoutPartial(): void
    {
        $logger = $this->createMock(Logger::class);
        $name   = 'Mezzio\\Navigation\\Top';

        $resource               = 'testResource';
        $privilege              = 'testPrivilege';
        $label                  = 'testLabel';
        $tranalatedLabel        = 'testLabelTranslated';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $tranalatedTitle        = 'testTitleTranslated';
        $textDomain             = 'testDomain';
        $id                     = 'testId';
        $class                  = 'test-class';
        $href                   = '#';
        $target                 = null;

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
        $page->expects(self::once())
            ->method('getLabel')
            ->willReturn($label);
        $page->expects(self::exactly(2))
            ->method('getTextDomain')
            ->willReturn($textDomain);
        $page->expects(self::once())
            ->method('getTitle')
            ->willReturn($title);
        $page->expects(self::once())
            ->method('getId')
            ->willReturn($id);
        $page->expects(self::once())
            ->method('getClass')
            ->willReturn($class);
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($href);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($target);

        $parentPage->addPage($page);

        $container = new Navigation();
        $container->addPage($parentPage);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

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

        $expected  = '<a parent-id-escaped="parent-id-escaped" parent-title-escaped="parent-title-escaped" parent-class-escaped="parent-class-escaped" parent-href-escaped="##-escaped" parent-target-escaped="self-escaped">parent-label-escaped</a>/<a idEscaped="testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelTranslatedAndEscaped</a>';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(11))
            ->method('__invoke')
            ->withConsecutive(
                [$tranalatedLabel],
                ['id'],
                ['title'],
                ['class'],
                ['href'],
                ['translated-parent-label'],
                ['id'],
                ['title'],
                ['class'],
                ['href'],
                ['target']
            )
            ->willReturnOnConsecutiveCalls(
                $escapedTranalatedLabel,
                'idEscaped',
                'titleEscaped',
                'classEscaped',
                'hrefEscaped',
                'parent-label-escaped',
                'parent-id-escaped',
                'parent-title-escaped',
                'parent-class-escaped',
                'parent-href-escaped',
                'parent-target-escaped'
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(9))
            ->method('__invoke')
            ->withConsecutive(
                [$id],
                [$tranalatedTitle],
                [$class],
                [$href],
                ['parent-id'],
                ['translated-parent-title'],
                ['parent-class'],
                ['##'],
                ['self']
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                'testClassEscaped',
                '#Escaped',
                'parent-id-escaped',
                'parent-title-escaped',
                'parent-class-escaped',
                '##-escaped',
                'self-escaped'
            );

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive([$label, $textDomain], [$title, $textDomain], ['parent-label', 'parent-text-domain'], ['parent-title', 'parent-text-domain'])
            ->willReturnOnConsecutiveCalls($tranalatedLabel, $tranalatedTitle, 'translated-parent-label', 'translated-parent-title');

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::exactly(2))
            ->method('has')
            ->with('translate')
            ->willReturn(true);
        $viewPluginManager->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(['translate'], ['escapeHtml'], ['translate'], ['escapeHtml'])
            ->willReturnOnConsecutiveCalls($translatePlugin, $escapeHtml, $translatePlugin, $escapeHtml);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::exactly(4))
            ->method('plugin')
            ->withConsecutive(['escapehtml'], ['escapehtmlattr'], ['escapehtml'], ['escapehtmlattr'])
            ->willReturnOnConsecutiveCalls($escapeHtml, $escapeHtmlAttr, $escapeHtml, $escapeHtmlAttr);
        $view->expects(self::exactly(2))
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->render($name));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderWithPartial(): void
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
            ->method('isActive');
        $page->expects(self::never())
            ->method('getParent');

        $container = new Navigation();
        $container->addPage($page);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        $role = 'testRole';

        $helper->setRole($role);

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects(self::never())
            ->method('isGranted');

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        $expected  = 'renderedPartial';
        $partial   = 'testPartial';
        $seperator = '/';

        $helper->setSeparator($seperator);
        $helper->setLinkLast(true);
        $helper->setPartial($partial);

        $partialHelper = $this->getMockBuilder(Partial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $partialHelper->expects(self::once())
            ->method('__invoke')
            ->with($partial, ['pages' => [], 'separator' => $seperator])
            ->willReturn($expected);

        $viewPluginManager = $this->getMockBuilder(HelperPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewPluginManager->expects(self::never())
            ->method('has');
        $viewPluginManager->expects(self::once())
            ->method('get')
            ->with('partial')
            ->willReturn($partialHelper);

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::never())
            ->method('plugin');
        $view->expects(self::once())
            ->method('getHelperPluginManager')
            ->willReturn($viewPluginManager);

        /* @var PhpRenderer $view */
        $helper->setView($view);

        self::assertSame($expected, $helper->render($name));
    }
}
