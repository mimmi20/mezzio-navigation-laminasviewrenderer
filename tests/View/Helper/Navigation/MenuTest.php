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
use Laminas\Log\Logger;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Uri;
use PHPUnit\Framework\TestCase;

final class MenuTest extends TestCase
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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

        self::assertSame(0, $helper->getMinDepth());

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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Menu::setDefaultRole($defaultRole);

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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        /* @var AuthorizationInterface $defaultAuth */
        Menu::setDefaultAuthorization($defaultAuth);

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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

        $container1 = $helper->getContainer();

        self::assertInstanceOf(\Mezzio\Navigation\Navigation::class, $container1);

        /* @var AuthorizationInterface $auth */
        $helper->setContainer();

        $container2 = $helper->getContainer();

        self::assertInstanceOf(\Mezzio\Navigation\Navigation::class, $container2);
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
        $helper = new Menu($serviceLocator, $logger);

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
            ->with(\Mezzio\Navigation\Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(\Mezzio\Navigation\Navigation::class)
            ->willThrowException(new ServiceNotFoundException('test'));

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Menu($serviceLocator, $logger);

        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', \Mezzio\Navigation\Navigation::class));

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
            ->with(\Mezzio\Navigation\Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(\Mezzio\Navigation\Navigation::class)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Menu($serviceLocator, $logger);

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
            ->withConsecutive([\Mezzio\Navigation\Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Menu($serviceLocator, $logger);

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
            ->withConsecutive([\Mezzio\Navigation\Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Menu($serviceLocator, $logger);

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
            ->withConsecutive([\Mezzio\Navigation\Navigation::class], ['navigation'])
            ->willReturnOnConsecutiveCalls(false, false);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

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
        $translator->expects(self::exactly(2))
            ->method('translate')
            ->withConsecutive([$title, $textDomain], [$label, $textDomain])
            ->willReturnOnConsecutiveCalls($tranalatedTitle, $tranalatedLabel);

        /* @var TranslatorInterface $translator */
        $helper->setTranslator($translator);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(6))
            ->method('__invoke')
            ->withConsecutive(
                ['id'],
                ['title'],
                ['class'],
                ['href'],
                ['target'],
                [$tranalatedLabel]
            )
            ->willReturnOnConsecutiveCalls(
                'idEscaped',
                'titleEscaped',
                'classEscaped',
                'hrefEscaped',
                'targetEscaped',
                $escapedTranalatedLabel
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(
                ['testId'],
                [$tranalatedTitle],
                ['test-class'],
                ['#'],
                ['_blank']
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                'testClassEscaped',
                '#Escaped',
                '_blankEscaped'
            );

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::exactly(3))
            ->method('plugin')
            ->withConsecutive(['escapehtml'], ['escapehtmlattr'], ['escapeHtml'])
            ->willReturnOnConsecutiveCalls($escapeHtml, $escapeHtmlAttr, $escapeHtml);

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
        $helper = new Menu($serviceLocator, $logger);

        $helper->setContainer($name);

        $label                  = 'testLabel';
        $escapedTranalatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $textDomain             = 'testDomain';
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
                ['id'],
                ['title'],
                ['class'],
                ['href'],
                [$label]
            )
            ->willReturnOnConsecutiveCalls(
                'idEscaped',
                'titleEscaped',
                'classEscaped',
                'hrefEscaped',
                $escapedTranalatedLabel
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

        $view = $this->getMockBuilder(PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects(self::exactly(3))
            ->method('plugin')
            ->withConsecutive(['escapehtml'], ['escapehtmlattr'], ['escapeHtml'])
            ->willReturnOnConsecutiveCalls($escapeHtml, $escapeHtmlAttr, $escapeHtml);

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
        $helper = new Menu($serviceLocator, $logger);

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

        $container = new \Mezzio\Navigation\Navigation();
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
        $helper = new Menu($serviceLocator, $logger);

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

        $container = new \Mezzio\Navigation\Navigation();
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
        $helper = new Menu($serviceLocator, $logger);

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
        $helper = new Menu($serviceLocator, $logger);

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

        $container = new \Mezzio\Navigation\Navigation();
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
        $helper = new Menu($serviceLocator, $logger);

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

        $container = new \Mezzio\Navigation\Navigation();
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
        $helper = new Menu($serviceLocator, $logger);

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

        $container = new \Mezzio\Navigation\Navigation();
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
        $helper = new Menu($serviceLocator, $logger);

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
}
