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
namespace MezzioTest\Navigation\LaminasView\Helper;

use Laminas\I18n\View\Helper\Translate;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Mezzio\Navigation\LaminasView\Helper\Htmlify;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mezzio\Navigation\Page\PageInterface;
use PHPUnit\Framework\TestCase;

final class HtmlifyTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlify(): void
    {
        $expected = '<a id="breadcrumbs-testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslatedAndEscaped</a>';

        $label                  = 'testLabel';
        $translatedLabel        = 'testLabelTranslated';
        $escapedTranslatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $tranalatedTitle        = 'testTitleTranslated';
        $textDomain             = 'testDomain';
        $id                     = 'testId';
        $class                  = 'test-class';
        $href                   = '#';
        $target                 = '_blank';

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$label, $textDomain], [$title, $textDomain])
            ->willReturnOnConsecutiveCalls($translatedLabel, $tranalatedTitle);

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
                [$translatedLabel]
            )
            ->willReturnOnConsecutiveCalls(
                'id',
                'titleEscaped',
                'classEscaped',
                'hrefEscaped',
                'targetEscaped',
                $escapedTranslatedLabel
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
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);

        \assert($escapeHtml instanceof EscapeHtml);
        \assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        \assert($translatePlugin instanceof Translate);
        $helper = new Htmlify($escapeHtml, $escapeHtmlAttr, $translatePlugin);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->toHtml(Breadcrumbs::class, $page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlifyWithoutTranslator(): void
    {
        $expected = '<a id="breadcrumbs-testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabelEscaped</a>';

        $label        = 'testLabel';
        $escapedLabel = 'testLabelEscaped';
        $title        = 'testTitle';
        $id           = 'testId';
        $class        = 'test-class';
        $href         = '#';
        $target       = null;

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
                'id',
                'titleEscaped',
                'classEscaped',
                'hrefEscaped',
                $escapedLabel
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
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);

        \assert($escapeHtml instanceof EscapeHtml);
        \assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        $helper = new Htmlify($escapeHtml, $escapeHtmlAttr);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->toHtml(Breadcrumbs::class, $page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlifyWithoutEscapingLabel(): void
    {
        $expected = '<a id="breadcrumbs-testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslated</a>';

        $label           = 'testLabel';
        $translatedLabel = 'testLabelTranslated';
        $title           = 'testTitle';
        $tranalatedTitle = 'testTitleTranslated';
        $textDomain      = 'testDomain';
        $id              = 'testId';
        $class           = 'test-class';
        $href            = '#';
        $target          = '_blank';

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$label, $textDomain], [$title, $textDomain])
            ->willReturnOnConsecutiveCalls($translatedLabel, $tranalatedTitle);

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
                ['target']
            )
            ->willReturnOnConsecutiveCalls(
                'id',
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
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);

        \assert($escapeHtml instanceof EscapeHtml);
        \assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        \assert($translatePlugin instanceof Translate);
        $helper = new Htmlify($escapeHtml, $escapeHtmlAttr, $translatePlugin);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->toHtml(Breadcrumbs::class, $page, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlifyWithoutTranslatorAndEscapingLabel(): void
    {
        $expected = '<a id="breadcrumbs-testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped">testLabel</a>';

        $label  = 'testLabel';
        $title  = 'testTitle';
        $id     = 'testId';
        $class  = 'test-class';
        $href   = '#';
        $target = null;

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(
                ['id'],
                ['title'],
                ['class'],
                ['href']
            )
            ->willReturnOnConsecutiveCalls(
                'id',
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
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);

        \assert($escapeHtml instanceof EscapeHtml);
        \assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        $helper = new Htmlify($escapeHtml, $escapeHtmlAttr);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->toHtml(Breadcrumbs::class, $page, false));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlifyWithClassOnListItem(): void
    {
        $expected = '<a id="breadcrumbs-testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped">testLabelTranslatedAndEscaped</a>';

        $label                  = 'testLabel';
        $translatedLabel        = 'testLabelTranslated';
        $escapedTranslatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $tranalatedTitle        = 'testTitleTranslated';
        $textDomain             = 'testDomain';
        $id                     = 'testId';
        $href                   = '#';
        $target                 = '_blank';

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$label, $textDomain], [$title, $textDomain])
            ->willReturnOnConsecutiveCalls($translatedLabel, $tranalatedTitle);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(5))
            ->method('__invoke')
            ->withConsecutive(
                ['id'],
                ['title'],
                ['href'],
                ['target'],
                [$translatedLabel]
            )
            ->willReturnOnConsecutiveCalls(
                'id',
                'titleEscaped',
                'hrefEscaped',
                'targetEscaped',
                $escapedTranslatedLabel
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(
                [$id],
                [$tranalatedTitle],
                [$href],
                [$target]
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                '#Escaped',
                '_blankEscaped'
            );

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
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($href);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($target);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);

        \assert($escapeHtml instanceof EscapeHtml);
        \assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        \assert($translatePlugin instanceof Translate);
        $helper = new Htmlify($escapeHtml, $escapeHtmlAttr, $translatePlugin);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->toHtml(Breadcrumbs::class, $page, true, true));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlifyWithoutTranslatorAndWithClassOnListItem(): void
    {
        $expected = '<a id="breadcrumbs-testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" hrefEscaped="#Escaped">testLabelEscaped</a>';

        $label        = 'testLabel';
        $escapedLabel = 'testLabelEscaped';
        $title        = 'testTitle';
        $id           = 'testId';
        $href         = '#';
        $target       = null;

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(
                ['id'],
                ['title'],
                ['href'],
                [$label]
            )
            ->willReturnOnConsecutiveCalls(
                'id',
                'titleEscaped',
                'hrefEscaped',
                $escapedLabel
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(3))
            ->method('__invoke')
            ->withConsecutive(
                [$id],
                [$title],
                [$href]
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                '#Escaped'
            );

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
        $page->expects(self::never())
            ->method('getClass');
        $page->expects(self::once())
            ->method('getHref')
            ->willReturn($href);
        $page->expects(self::once())
            ->method('getTarget')
            ->willReturn($target);
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);

        \assert($escapeHtml instanceof EscapeHtml);
        \assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        $helper = new Htmlify($escapeHtml, $escapeHtmlAttr);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->toHtml(Breadcrumbs::class, $page, true, true));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlifyWithoutHref(): void
    {
        $expected = '<span id="breadcrumbs-testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped">testLabelTranslatedAndEscaped</span>';

        $label                  = 'testLabel';
        $translatedLabel        = 'testLabelTranslated';
        $escapedTranslatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $tranalatedTitle        = 'testTitleTranslated';
        $textDomain             = 'testDomain';
        $id                     = 'testId';
        $class                  = 'test-class';

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$label, $textDomain], [$title, $textDomain])
            ->willReturnOnConsecutiveCalls($translatedLabel, $tranalatedTitle);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(
                ['id'],
                ['title'],
                ['class'],
                [$translatedLabel]
            )
            ->willReturnOnConsecutiveCalls(
                'id',
                'titleEscaped',
                'classEscaped',
                $escapedTranslatedLabel
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(3))
            ->method('__invoke')
            ->withConsecutive(
                [$id],
                [$tranalatedTitle],
                [$class]
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                'testClassEscaped'
            );

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
            ->willReturn('');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);

        \assert($escapeHtml instanceof EscapeHtml);
        \assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        \assert($translatePlugin instanceof Translate);
        $helper = new Htmlify($escapeHtml, $escapeHtmlAttr, $translatePlugin);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->toHtml(Breadcrumbs::class, $page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlifyWithoutTranslatorAndHref(): void
    {
        $expected = '<span id="breadcrumbs-testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped">testLabelEscaped</span>';

        $label        = 'testLabel';
        $escapedLabel = 'testLabelEscaped';
        $title        = 'testTitle';
        $id           = 'testId';
        $class        = 'test-class';

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(4))
            ->method('__invoke')
            ->withConsecutive(
                ['id'],
                ['title'],
                ['class'],
                [$label]
            )
            ->willReturnOnConsecutiveCalls(
                'id',
                'titleEscaped',
                'classEscaped',
                $escapedLabel
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(3))
            ->method('__invoke')
            ->withConsecutive(
                [$id],
                [$title],
                [$class]
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                'testClassEscaped'
            );

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
            ->willReturn('');
        $page->expects(self::never())
            ->method('getTarget');
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn([]);

        \assert($escapeHtml instanceof EscapeHtml);
        \assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        $helper = new Htmlify($escapeHtml, $escapeHtmlAttr);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->toHtml(Breadcrumbs::class, $page));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testHtmlifyWithArrayOfClasses(): void
    {
        $expected = '<a id="breadcrumbs-testIdEscaped" titleEscaped="testTitleTranslatedAndEscaped" classEscaped="testClassEscaped" hrefEscaped="#Escaped" targetEscaped="_blankEscaped" onClick=\'{"a":"b"}\' data-test="test-class1 test-class2">testLabelTranslatedAndEscaped</a>';

        $label                  = 'testLabel';
        $translatedLabel        = 'testLabelTranslated';
        $escapedTranslatedLabel = 'testLabelTranslatedAndEscaped';
        $title                  = 'testTitle';
        $tranalatedTitle        = 'testTitleTranslated';
        $textDomain             = 'testDomain';
        $id                     = 'testId';
        $class                  = 'test-class';
        $href                   = '#';
        $target                 = '_blank';

        $translatePlugin = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatePlugin->expects(self::exactly(2))
            ->method('__invoke')
            ->withConsecutive([$label, $textDomain], [$title, $textDomain])
            ->willReturnOnConsecutiveCalls($translatedLabel, $tranalatedTitle);

        $escapeHtml = $this->getMockBuilder(EscapeHtml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtml->expects(self::exactly(8))
            ->method('__invoke')
            ->withConsecutive(
                ['id'],
                ['title'],
                ['class'],
                ['href'],
                ['target'],
                ['onClick'],
                ['data-test'],
                [$translatedLabel]
            )
            ->willReturnOnConsecutiveCalls(
                'id',
                'titleEscaped',
                'classEscaped',
                'hrefEscaped',
                'targetEscaped',
                'onClick',
                'data-test',
                $escapedTranslatedLabel
            );

        $escapeHtmlAttr = $this->getMockBuilder(EscapeHtmlAttr::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escapeHtmlAttr->expects(self::exactly(7))
            ->method('__invoke')
            ->withConsecutive(
                [$id],
                [$tranalatedTitle],
                [$class],
                [$href],
                [$target],
                ['{"a":"b"}'],
                ['test-class1 test-class2']
            )
            ->willReturnOnConsecutiveCalls(
                'testIdEscaped',
                'testTitleTranslatedAndEscaped',
                'testClassEscaped',
                '#Escaped',
                '_blankEscaped',
                '{"a":"b"}',
                'test-class1 test-class2'
            );

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
        $page->expects(self::once())
            ->method('getCustomProperties')
            ->willReturn(['onClick' => (object) ['a' => 'b'], 'data-test' => ['test-class1', 'test-class2']]);

        \assert($escapeHtml instanceof EscapeHtml);
        \assert($escapeHtmlAttr instanceof EscapeHtmlAttr);
        \assert($translatePlugin instanceof Translate);
        $helper = new Htmlify($escapeHtml, $escapeHtmlAttr, $translatePlugin);

        /* @var PageInterface $page */
        self::assertSame($expected, $helper->toHtml(Breadcrumbs::class, $page));
    }
}
