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
namespace MezzioTest\Navigation\LaminasView\Compare;

use Laminas\Config\Config;
use Laminas\Log\Logger;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource;
use Laminas\Permissions\Acl\Role\GenericRole;
use Laminas\View\Exception\DomainException;
use Laminas\View\Helper\HeadLink;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\GenericAuthorization\Acl\LaminasAcl;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\FindRootInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mezzio\Navigation\Page\PageFactory;
use Mezzio\Navigation\Page\PageInterface;
use Mezzio\Navigation\Page\Uri;

/**
 * Tests Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
final class LinksTest extends AbstractTest
{
    /**
     * Class name for view helper to test
     *
     * @var string
     */
    protected $helperName = Navigation\Links::class;

    /**
     * View helper
     *
     * @var Navigation\Links
     */
    protected $helper;

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Laminas\Config\Exception\InvalidArgumentException
     * @throws \Laminas\Config\Exception\RuntimeException
     * @throws \ErrorException
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

//        // doctype fix (someone forgot to clean up after their unit tests)
//        $this->_doctypeHelper = $this->helper->getView()->plugin('doctype');
//        $this->_oldDoctype = $this->_doctypeHelper->getDoctype();
//        $this->_doctypeHelper->setDoctype(
//            \Laminas\View\Helper\Doctype::HTML4_LOOSE
//        );

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(self::never())
            ->method('log');
        $logger->expects(self::never())
            ->method('emerg');
        $logger->expects(self::never())
            ->method('alert');
        $logger->expects(self::never())
            ->method('crit');
        $logger->expects(self::never())
            ->method('err');
        $logger->expects(self::never())
            ->method('warn');
        $logger->expects(self::never())
            ->method('notice');
        $logger->expects(self::never())
            ->method('info');
        $logger->expects(self::never())
            ->method('debug');

        $helperPluginManager = $this->serviceManager->get(HelperPluginManager::class);
        $plugin              = $this->serviceManager->get(ViewHelperPluginManager::class);

        $baseUrlHelper = $this->serviceManager->get(BaseServerUrlHelper::class);
        \assert(
            $baseUrlHelper instanceof BaseServerUrlHelper,
            sprintf(
                '$baseUrlHelper should be an Instance of %s, but was %s',
                BaseServerUrlHelper::class,
                get_class($baseUrlHelper)
            )
        );

        $headLinkHelper = $plugin->get(HeadLink::class);
        \assert(
            $headLinkHelper instanceof HeadLink,
            sprintf(
                '$headLinkHelper should be an Instance of %s, but was %s',
                HeadLink::class,
                get_class($headLinkHelper)
            )
        );

        // create helper
        $this->helper = new Navigation\Links(
            $this->serviceManager,
            $logger,
            $helperPluginManager->get(HtmlifyInterface::class),
            $helperPluginManager->get(ContainerParserInterface::class),
            $helperPluginManager->get(FindRootInterface::class),
            $headLinkHelper
        );

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);

        // disable all active pages
        foreach ($this->helper->findAllByActive(true) as $page) {
            $page->active = false;
        }
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
    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = $this->helper->__invoke();
        self::assertEquals($this->helper, $returned);
        self::assertEquals($this->nav1, $returned->getContainer());
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
    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = $this->helper->__invoke($this->nav2);
        self::assertEquals($this->helper, $returned);
        self::assertEquals($this->nav2, $returned->getContainer());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     *
     * @return void
     */
    public function testDoNotRenderIfNoPageIsActive(): void
    {
        self::assertEquals('', $this->helper->render());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testDetectRelationFromStringPropertyOfActivePage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $active->addRel('example', 'http://www.example.com/');
        $found = $this->helper->findRelation($active, 'rel', 'example');

        $expected = [
            'type' => Uri::class,
            'href' => 'http://www.example.com/',
            'label' => null,
        ];

        self::assertIsObject($found);
        self::assertInstanceOf(PageInterface::class, $found);

        $actual = [
            'type' => get_class($found),
            'href' => $found->getHref(),
            'label' => $found->getLabel(),
        ];

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testDetectRelationFromPageInstancePropertyOfActivePage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $active->addRel('example', (new PageFactory())->factory([
            'uri' => 'http://www.example.com/',
            'label' => 'An example page',
        ]));
        $found = $this->helper->findRelExample($active);

        self::assertIsObject($found);
        self::assertInstanceOf(PageInterface::class, $found);

        $expected = [
            'type' => Uri::class,
            'href' => 'http://www.example.com/',
            'label' => 'An example page',
        ];

        $actual = [
            'type' => get_class($found),
            'href' => $found->getHref(),
            'label' => $found->getLabel(),
        ];

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testDetectRelationFromArrayPropertyOfActivePage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $active->addRel('example', [
            'uri' => 'http://www.example.com/',
            'label' => 'An example page',
        ]);
        $found = $this->helper->findRelExample($active);

        self::assertIsObject($found);
        self::assertInstanceOf(PageInterface::class, $found);

        $expected = [
            'type' => Uri::class,
            'href' => 'http://www.example.com/',
            'label' => 'An example page',
        ];

        $actual = [
            'type' => get_class($found),
            'href' => $found->getHref(),
            'label' => $found->getLabel(),
        ];

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testDetectRelationFromConfigInstancePropertyOfActivePage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $active->addRel('example', new Config([
            'uri' => 'http://www.example.com/',
            'label' => 'An example page',
        ]));
        $found = $this->helper->findRelExample($active);

        self::assertIsObject($found);
        self::assertInstanceOf(PageInterface::class, $found);

        $expected = [
            'type' => Uri::class,
            'href' => 'http://www.example.com/',
            'label' => 'An example page',
        ];

        $actual = [
            'type' => get_class($found),
            'href' => $found->getHref(),
            'label' => $found->getLabel(),
        ];

        self::assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @ throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @ throws \Laminas\View\Exception\InvalidArgumentException
     * @ throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testDetectMultipleRelationsFromArrayPropertyOfActivePage(): void
    {
        self::markTestSkipped();
//        $active = $this->helper->findOneByLabel('Page 2');
//
//
//        self::assertInstanceOf(PageInterface::class, $active);
//
//        $active->addRel('alternate', [
//            [
//                'label' => 'foo',
//                'uri'   => 'bar'
//            ],
//            [
//                'label' => 'baz',
//                'uri'   => 'bat'
//            ]
//        ]);
//
//        $found = $this->helper->findRelAlternate($active);
//
//        self::assertIsObject($found);
//        self::assertInstanceOf(PageInterface::class, $found);
//
//        $expected = ['type' => 'array', 'count' => 2];
//        $actual = ['type' => gettype($found), 'count' => count($found)];
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @ throws \PHPUnit\Framework\ExpectationFailedException
     * @ throws \PHPUnit\Framework\MockObject\RuntimeException
     * @ throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    public function testDetectMultipleRelationsFromConfigPropertyOfActivePage(): void
    {
        self::markTestSkipped();
//        $active = $this->helper->findOneByLabel('Page 2');
//
//
//        self::assertInstanceOf(PageInterface::class, $active);
//
//        $active->addRel('alternate', new Config([
//            [
//                'label' => 'foo',
//                'uri'   => 'bar'
//            ],
//            [
//                'label' => 'baz',
//                'uri'   => 'bat'
//            ]
//        ]));
//
//        $found = $this->helper->findRelAlternate($active);
//
//        $expected = ['type' => 'array', 'count' => 2];
//        $actual = ['type' => gettype($found), 'count' => count($found)];
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testExtractingRelationsFromPageProperties(): void
    {
        $types = [
            'alternate',
            'stylesheet',
            'start',
            'next',
            'prev',
            'contents',
            'index',
            'glossary',
            'copyright',
            'chapter',
            'section',
            'subsection',
            'appendix',
            'help',
            'bookmark',
        ];

        $samplePage = (new PageFactory())->factory([
            'label' => 'An example page',
            'uri' => 'http://www.example.com/',
        ]);

        self::assertIsObject($samplePage);
        self::assertInstanceOf(PageInterface::class, $samplePage);

        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = [];
        $actual   = [];

        foreach ($types as $type) {
            $active->addRel($type, $samplePage);
            $found = $this->helper->findRelation($active, 'rel', $type);

            self::assertIsObject($found);
            self::assertInstanceOf(PageInterface::class, $found);

            $expected[$type] = $samplePage->getLabel();
            $actual[$type]   = $found->getLabel();

            $active->removeRel($type);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindStartPageByTraversal(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Home';
        $actual   = $this->helper->findRelStart($active)->getLabel();
        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testDoNotFindStartWhenGivenPageIsTheFirstPage(): void
    {
        $active = $this->helper->findOneByLabel('Home');

        self::assertInstanceOf(PageInterface::class, $active);

        $actual = $this->helper->findRelStart($active);
        self::assertNull($actual, 'Should not find any start page');
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindNextPageByTraversalShouldFindChildPage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.1';
        $actual   = $this->helper->findRelNext($active)->getLabel();
        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindNextPageByTraversalShouldFindSiblingPage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.2';
        $actual   = $this->helper->findRelNext($active)->getLabel();
        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindNextPageByTraversalShouldWrap(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2.2');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.3';
        $actual   = $this->helper->findRelNext($active)->getLabel();
        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindPrevPageByTraversalShouldFindParentPage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2';
        $actual   = $this->helper->findRelPrev($active)->getLabel();
        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindPrevPageByTraversalShouldFindSiblingPage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.1';
        $actual   = $this->helper->findRelPrev($active)->getLabel();
        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindPrevPageByTraversalShouldWrap(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.3');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.2.2';
        $actual   = $this->helper->findRelPrev($active)->getLabel();
        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testShouldFindChaptersFromFirstLevelOfPagesInContainer(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.3');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelChapter($active);

        $expected = ['Page 1', 'Page 2', 'Page 3', 'Zym'];
        $actual   = [];
        foreach ($found as $page) {
            $actual[] = $page->getLabel();
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindingChaptersShouldExcludeSelfIfChapter(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelChapter($active);

        $expected = ['Page 1', 'Page 3', 'Zym'];
        $actual   = [];
        foreach ($found as $page) {
            $actual[] = $page->getLabel();
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindSectionsWhenActiveChapterPage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $found    = $this->helper->findRelSection($active);
        $expected = ['Page 2.1', 'Page 2.2', 'Page 2.3'];
        $actual   = [];
        foreach ($found as $page) {
            $actual[] = $page->getLabel();
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testDoNotFindSectionsWhenActivePageIsASection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelSection($active);
        self::assertNull($found);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testDoNotFindSectionsWhenActivePageIsASubsection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelation($active, 'rel', 'section');
        self::assertNull($found);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindSubsectionWhenActivePageIsSection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelSubsection($active);

        $expected = ['Page 2.2.1', 'Page 2.2.2'];
        $actual   = [];
        foreach ($found as $page) {
            $actual[] = $page->getLabel();
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testDoNotFindSubsectionsWhenActivePageIsASubSubsection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelSubsection($active);
        self::assertNull($found);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testDoNotFindSubsectionsWhenActivePageIsAChapter(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelSubsection($active);
        self::assertNull($found);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindRevSectionWhenPageIsSection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRevSection($active);
        self::assertEquals('Page 2', $found->getLabel());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindRevSubsectionWhenPageIsSubsection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRevSubsection($active);
        self::assertEquals('Page 2.2', $found->getLabel());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     * @throws \ErrorException
     *
     * @return void
     */
    public function testAclFiltersAwayPagesFromPageProperty(): void
    {
        $acl = new Acl();
        $acl->addRole(new GenericRole('member'));
        $acl->addRole(new GenericRole('admin'));
        $acl->addResource(new GenericResource('protected'));
        $acl->allow('admin', 'protected');

        $this->helper->setAuthorization(new LaminasAcl($acl));
        $this->helper->setRole('member');

        $samplePage = (new PageFactory())->factory([
            'label' => 'An example page',
            'uri' => 'http://www.example.com/',
            'resource' => 'protected',
        ]);

        self::assertInstanceOf(PageInterface::class, $samplePage);

        $active = $this->helper->findOneByLabel('Home');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = [
            'alternate' => false,
            'stylesheet' => false,
            'start' => false,
            'next' => 'Page 1',
            'prev' => false,
            'contents' => false,
            'index' => false,
            'glossary' => false,
            'copyright' => false,
            'chapter' => 'array(4)',
            'section' => false,
            'subsection' => false,
            'appendix' => false,
            'help' => false,
            'bookmark' => false,
        ];
        $actual = [];

        foreach ($expected as $type => $discard) {
            $active->addRel($type, $samplePage);

            $found = $this->helper->findRelation($active, 'rel', $type);

            if (null === $found) {
                $actual[$type] = false;
            } elseif (is_array($found)) {
                $actual[$type] = 'array(' . count($found) . ')';
            } else {
                $actual[$type] = $found->getLabel();
            }
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     * @throws \ErrorException
     *
     * @return void
     */
    public function testAclFiltersAwayPagesFromContainerSearch(): void
    {
        $acl = new Acl();
        $acl->addRole(new GenericRole('member'));
        $acl->addRole(new GenericRole('admin'));
        $acl->addResource(new GenericResource('protected'));
        $acl->allow('admin', 'protected');

        $this->helper->setAuthorization(new LaminasAcl($acl));
        $this->helper->setRole('member');

        $container = $this->helper->getContainer();
        $iterator  = new \RecursiveIteratorIterator(
            $container,
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $page) {
            $page->resource = 'protected';
        }

        $this->helper->setContainer($container);

        $active = $this->helper->findOneByLabel('Home');

        self::assertInstanceOf(PageInterface::class, $active);

        $search = [
            'start' => 'Page 1',
            'next' => 'Page 1',
            'prev' => 'Page 1.1',
            'chapter' => 'Home',
            'section' => 'Page 1',
            'subsection' => 'Page 2.2',
        ];

        $expected = [];
        $actual   = [];

        foreach ($search as $type => $activeLabel) {
            $expected[$type] = false;

            $active = $this->helper->findOneByLabel($activeLabel);

            self::assertInstanceOf(PageInterface::class, $active);

            $found = $this->helper->findRelation($active, 'rel', $type);

            if (null === $found) {
                $actual[$type] = false;
            } elseif (is_array($found)) {
                $actual[$type] = 'array(' . count($found) . ')';
            } else {
                $actual[$type] = $found->getLabel();
            }
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindRelationMustSpecifyRelOrRev(): void
    {
        $active = $this->helper->findOneByLabel('Home');

        self::assertInstanceOf(PageInterface::class, $active);

        try {
            $this->helper->findRelation($active, 'foo', 'bar');

            self::fail('An invalid value was given, but a Laminas\View\Exception\InvalidArgumentException was not thrown');
        } catch (DomainException $e) {
            self::assertContains('Invalid argument: $rel', $e->getMessage());
        }
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testRenderLinkMustSpecifyRelOrRev(): void
    {
        $active = $this->helper->findOneByLabel('Home');

        self::assertInstanceOf(PageInterface::class, $active);

        try {
            $this->helper->renderLink($active, 'foo', 'bar');

            self::fail('An invalid value was given, but a ' .
                        'Laminas\View\Exception\InvalidArgumentException was not thrown');
        } catch (DomainException $e) {
            self::assertContains('Invalid relation attribute', $e->getMessage());
        }
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testFindAllRelations(): void
    {
        $expectedRelations = [
            'alternate' => ['Forced page'],
            'stylesheet' => ['Forced page'],
            'start' => ['Forced page'],
            'next' => ['Forced page'],
            'prev' => ['Forced page'],
            'contents' => ['Forced page'],
            'index' => ['Forced page'],
            'glossary' => ['Forced page'],
            'copyright' => ['Forced page'],
            'chapter' => ['Forced page'],
            'section' => ['Forced page'],
            'subsection' => ['Forced page'],
            'appendix' => ['Forced page'],
            'help' => ['Forced page'],
            'bookmark' => ['Forced page'],
            'canonical' => ['Forced page'],
            'home' => ['Forced page'],
        ];

        // build expected result
        $expected = [
            'rel' => $expectedRelations,
            'rev' => $expectedRelations,
        ];

        // find active page and create page to use for relations
        $active = $this->helper->findOneByLabel('Page 1');

        self::assertInstanceOf(PageInterface::class, $active);

        $forcedRelation = new Uri([
            'label' => 'Forced page',
            'uri' => '#',
        ]);

        // add relations to active page
        foreach ($expectedRelations as $type => $discard) {
            $active->addRel($type, $forcedRelation);
            $active->addRev($type, $forcedRelation);
        }

        // build actual result
        $actual = $this->helper->findAllRelations($active);

        foreach ($actual as $attrib => $relations) {
            foreach ($relations as $type => $pages) {
                foreach ($pages as $key => $page) {
                    $actual[$attrib][$type][$key] = $page->getLabel();
                }
            }
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    private function getFlags(): array
    {
        return [
            Navigation\Links::RENDER_ALTERNATE => 'alternate',
            Navigation\Links::RENDER_STYLESHEET => 'stylesheet',
            Navigation\Links::RENDER_START => 'start',
            Navigation\Links::RENDER_NEXT => 'next',
            Navigation\Links::RENDER_PREV => 'prev',
            Navigation\Links::RENDER_CONTENTS => 'contents',
            Navigation\Links::RENDER_INDEX => 'index',
            Navigation\Links::RENDER_GLOSSARY => 'glossary',
            Navigation\Links::RENDER_CHAPTER => 'chapter',
            Navigation\Links::RENDER_SECTION => 'section',
            Navigation\Links::RENDER_SUBSECTION => 'subsection',
            Navigation\Links::RENDER_APPENDIX => 'appendix',
            Navigation\Links::RENDER_HELP => 'help',
            Navigation\Links::RENDER_BOOKMARK => 'bookmark',
            Navigation\Links::RENDER_CUSTOM => 'canonical',
        ];
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testSingleRenderFlags(): void
    {
        $active = $this->helper->findOneByLabel('Home');

        self::assertInstanceOf(PageInterface::class, $active);

        $active->setActive(true);

        $expected = [];
        $actual   = [];

        // build expected and actual result
        foreach ($this->getFlags() as $newFlag => $type) {
            // add forced relation
            $active->addRel($type, 'http://www.example.com/');
            $active->addRev($type, 'http://www.example.com/');

            $this->helper->setRenderFlag($newFlag);
            $expectedOutput = '<link '
                              . 'href="http&#x3A;&#x2F;&#x2F;www.example.com&#x2F;" '
                              . 'rel="' . $type . '" '
                              . '/>' . PHP_EOL
                            . '<link '
                              . 'href="http&#x3A;&#x2F;&#x2F;www.example.com&#x2F;" '
                              . 'rev="' . $type . '" '
                              . '/>';
            $actualOutput = $this->helper->render();

            $expected[$type] = $expectedOutput;
            $actual[$type]   = $actualOutput;

            // remove forced relation
            $active->removeRel($type);
            $active->removeRev($type);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testRenderFlagBitwiseOr(): void
    {
        $newFlag = Navigation\Links::RENDER_NEXT |
                   Navigation\Links::RENDER_PREV;
        $this->helper->setRenderFlag($newFlag);
        $active = $this->helper->findOneByLabel('Page 1.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $active->setActive(true);

        // test data
        $expected = '<link href="page2" rel="next" title="Page&#x20;2" />'
                  . PHP_EOL
                  . '<link href="page1" rel="prev" title="Page&#x20;1" />';
        $actual = $this->helper->render();

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testIndenting(): void
    {
        $active = $this->helper->findOneByLabel('Page 1.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $newFlag = Navigation\Links::RENDER_NEXT |
                   Navigation\Links::RENDER_PREV;
        $this->helper->setRenderFlag($newFlag);
        $this->helper->setIndent('  ');
        $active->setActive(true);

        // build expected and actual result
        $expected = '  <link href="page2" rel="next" title="Page&#x20;2" />'
                  . PHP_EOL
                  . '  <link href="page1" rel="prev" title="Page&#x20;1" />';
        $actual = $this->helper->render();

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testSetMaxDepth(): void
    {
        $this->helper->setMaxDepth(1);
        $this->helper->findOneByLabel('Page 2.3.3')->setActive(); // level 2
        $flag = Navigation\Links::RENDER_NEXT;

        $expected = '<link href="page2&#x2F;page2_3&#x2F;page2_3_1" rel="next" title="Page&#x20;2.3.1" />';
        $actual   = $this->helper->setRenderFlag($flag)->render();

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\View\Exception\ExceptionInterface
     * @throws \ErrorException
     *
     * @return void
     */
    public function testSetMinDepth(): void
    {
        $this->helper->setMinDepth(2);
        $this->helper->findOneByLabel('Page 2.3')->setActive(); // level 1
        $flag = Navigation\Links::RENDER_NEXT;

        $expected = '';
        $actual   = $this->helper->setRenderFlag($flag)->render();

        self::assertEquals($expected, $actual);
    }

    /**
     * Returns the contens of the expected $file, normalizes newlines
     *
     * @param string $file
     *
     * @return string
     */
    protected function getExpected(string $file): string
    {
        return str_replace("\n", PHP_EOL, parent::getExpected($file));
    }
}
