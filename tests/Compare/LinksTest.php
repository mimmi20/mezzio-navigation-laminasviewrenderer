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

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Compare;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource;
use Laminas\Permissions\Acl\Role\GenericRole;
use Laminas\View\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\HeadLink;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mimmi20\Mezzio\GenericAuthorization\Acl\LaminasAcl;
use Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface;
use Mimmi20\Mezzio\Navigation\Page\PageFactory;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\Mezzio\Navigation\Page\Uri;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\ConvertToPages\ConvertToPagesInterface;
use Mimmi20\NavigationHelper\FindRoot\FindRootInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use Psr\Container\ContainerExceptionInterface;
use RecursiveIteratorIterator;

use function array_key_exists;
use function array_keys;
use function assert;
use function count;
use function get_debug_type;
use function sprintf;
use function str_replace;

use const PHP_EOL;

/**
 * Tests Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Links
 */
#[Group('Compare')]
#[Group('Laminas_View')]
#[Group('Laminas_View_Helper')]
final class LinksTest extends AbstractTestCase
{
    /**
     * View helper
     *
     * @var Navigation\Links
     */
    private ViewHelperInterface $helper;

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $plugin = $this->serviceManager->get(ViewHelperPluginManager::class);
        assert($plugin instanceof ViewHelperPluginManager);

        $this->serviceManager->get(LaminasViewRenderer::class);

        $baseUrlHelper = $this->serviceManager->get(BaseServerUrlHelper::class);
        assert(
            $baseUrlHelper instanceof BaseServerUrlHelper,
            sprintf(
                '$baseUrlHelper should be an Instance of %s, but was %s',
                BaseServerUrlHelper::class,
                get_debug_type($baseUrlHelper),
            ),
        );

        $headLinkHelper = $plugin->get(HeadLink::class);
        assert(
            $headLinkHelper instanceof HeadLink,
            sprintf(
                '$headLinkHelper should be an Instance of %s, but was %s',
                HeadLink::class,
                get_debug_type($headLinkHelper),
            ),
        );

        assert($headLinkHelper->getView() !== null, 'View was not set into Headlink Helper');

        $htmlify         = $this->serviceManager->get(HtmlifyInterface::class);
        $containerParser = $this->serviceManager->get(ContainerParserInterface::class);
        $findRoot        = $this->serviceManager->get(FindRootInterface::class);
        $converter       = $this->serviceManager->get(ConvertToPagesInterface::class);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);
        assert($findRoot instanceof FindRootInterface);
        assert($converter instanceof ConvertToPagesInterface);

        // create helper
        $this->helper = new Navigation\Links(
            htmlify: $htmlify,
            containerParser: $containerParser,
            convertToPages: $converter,
            rootFinder: $findRoot,
            headLink: $headLinkHelper,
        );

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);

        // disable all active pages
        foreach ($this->helper->findAllByActive(true) as $page) {
            $page->active = false;
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = ($this->helper)();
        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav1, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = ($this->helper)($this->nav2);
        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav2, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testDoNotRenderIfNoPageIsActive(): void
    {
        self::assertSame('', $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
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

        self::assertIsArray($found);
        self::assertCount(1, $found);
        self::assertContainsOnly(PageInterface::class, $found);

        $actual = [
            'type' => $found[0]::class,
            'href' => $found[0]->getHref(),
            'label' => $found[0]->getLabel(),
        ];

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        self::assertIsArray($found);
        self::assertCount(1, $found);
        self::assertContainsOnly(PageInterface::class, $found);

        $expected = [
            'type' => Uri::class,
            'href' => 'http://www.example.com/',
            'label' => 'An example page',
        ];

        $actual = [
            'type' => $found[0]::class,
            'href' => $found[0]->getHref(),
            'label' => $found[0]->getLabel(),
        ];

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        self::assertIsArray($found);
        self::assertCount(1, $found);
        self::assertContainsOnly(PageInterface::class, $found);

        $expected = [
            'type' => Uri::class,
            'href' => 'http://www.example.com/',
            'label' => 'An example page',
        ];

        $actual = [
            'type' => $found[0]::class,
            'href' => $found[0]->getHref(),
            'label' => $found[0]->getLabel(),
        ];

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
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

            self::assertIsArray($found);
            self::assertCount(1, $found);
            self::assertContainsOnly(PageInterface::class, $found);

            $expected[$type] = $samplePage->getLabel();
            $actual[$type]   = $found[0]->getLabel();

            $active->removeRel($type);
        }

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindStartPageByTraversal(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Home';
        $actual   = $this->helper->findRelStart($active)[0]->getLabel();
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testDoNotFindStartWhenGivenPageIsTheFirstPage(): void
    {
        $active = $this->helper->findOneByLabel('Home');

        self::assertInstanceOf(PageInterface::class, $active);

        $actual = $this->helper->findRelStart($active);
        self::assertSame([], $actual, 'Should not find any start page');
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindNextPageByTraversalShouldFindChildPage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.1';
        $actual   = $this->helper->findRelNext($active)[0]->getLabel();
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindNextPageByTraversalShouldFindSiblingPage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.2';
        $actual   = $this->helper->findRelNext($active)[0]->getLabel();
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindNextPageByTraversalShouldWrap(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2.2');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.3';
        $actual   = $this->helper->findRelNext($active)[0]->getLabel();
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindPrevPageByTraversalShouldFindParentPage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2';
        $actual   = $this->helper->findRelPrev($active)[0]->getLabel();
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindPrevPageByTraversalShouldFindSiblingPage(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.1';
        $actual   = $this->helper->findRelPrev($active)[0]->getLabel();
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindPrevPageByTraversalShouldWrap(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.3');

        self::assertInstanceOf(PageInterface::class, $active);

        $expected = 'Page 2.2.2';
        $actual   = $this->helper->findRelPrev($active)[0]->getLabel();
        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testDoNotFindSectionsWhenActivePageIsASection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelSection($active);
        self::assertSame([], $found);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     */
    public function testDoNotFindSectionsWhenActivePageIsASubsection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelation($active, 'rel', 'section');
        self::assertSame([], $found);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testDoNotFindSubsectionsWhenActivePageIsASubSubsection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelSubsection($active);

        self::assertSame([], $found);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testDoNotFindSubsectionsWhenActivePageIsAChapter(): void
    {
        $active = $this->helper->findOneByLabel('Page 2');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRelSubsection($active);
        self::assertSame([], $found);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindRevSectionWhenPageIsSection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRevSection($active);
        self::assertSame('Page 2', $found[0]->getLabel());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testFindRevSubsectionWhenPageIsSubsection(): void
    {
        $active = $this->helper->findOneByLabel('Page 2.2.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $found = $this->helper->findRevSubsection($active);
        self::assertSame('Page 2.2', $found[0]->getLabel());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     */
    public function testAclFiltersAwayPagesFromPageProperty(): void
    {
        $acl = new Acl();
        $acl->addRole(new GenericRole('member'));
        $acl->addRole(new GenericRole('admin'));
        $acl->addResource(new GenericResource('protected'));
        $acl->allow('admin', 'protected');

        $this->helper->setAuthorization(new LaminasAcl($acl));
        $this->helper->setRoles(['member']);

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
        $actual   = [];

        foreach (array_keys($expected) as $type) {
            $active->addRel($type, $samplePage);

            $found = $this->helper->findRelation($active, 'rel', $type);

            if ($found === []) {
                $actual[$type] = false;
            } elseif (1 < count($found)) {
                $actual[$type] = 'array(' . count($found) . ')';
            } else {
                $actual[$type] = $found[0]->getLabel();
            }
        }

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     */
    public function testAclFiltersAwayPagesFromContainerSearch(): void
    {
        $acl = new Acl();
        $acl->addRole(new GenericRole('member'));
        $acl->addRole(new GenericRole('admin'));
        $acl->addResource(new GenericResource('protected'));
        $acl->allow('admin', 'protected');

        $this->helper->setAuthorization(new LaminasAcl($acl));
        $this->helper->setRoles(['member']);

        $container = $this->helper->getContainer();
        $iterator  = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $page) {
            assert($page instanceof PageInterface);

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

            if ($found === []) {
                $actual[$type] = false;
            } elseif (1 < count($found)) {
                $actual[$type] = 'array(' . count($found) . ')';
            } else {
                $actual[$type] = $found[0]->getLabel();
            }
        }

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testFindRelationMustSpecifyRelOrRev(): void
    {
        $active = $this->helper->findOneByLabel('Home');

        self::assertInstanceOf(PageInterface::class, $active);

        try {
            $this->helper->findRelation($active, 'foo', 'bar');

            self::fail(
                'An invalid value was given, but a Laminas\View\Exception\InvalidArgumentException was not thrown',
            );
        } catch (DomainException $e) {
            self::assertStringContainsString('Invalid argument: $rel', $e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testRenderLinkMustSpecifyRelOrRev(): void
    {
        $active = $this->helper->findOneByLabel('Home');

        self::assertInstanceOf(PageInterface::class, $active);

        try {
            $this->helper->renderLink($active, 'foo', 'bar');

            self::fail(
                'An invalid value was given, but a Laminas\View\Exception\InvalidArgumentException was not thrown',
            );
        } catch (DomainException $e) {
            self::assertStringContainsString('Invalid relation attribute', $e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
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
        foreach (array_keys($expectedRelations) as $type) {
            $active->addRel($type, $forcedRelation);
            $active->addRev($type, $forcedRelation);
        }

        // build actual result
        $allRelations = $this->helper->findAllRelations($active);
        $actual       = [];

        foreach ($allRelations as $attrib => $relations) {
            if (!array_key_exists($attrib, $actual)) {
                $actual[$attrib] = [];
            }

            foreach ($relations as $type => $pages) {
                if (!array_key_exists($type, $actual[$attrib])) {
                    $actual[$attrib][$type] = [];
                }

                foreach ($pages as $key => $page) {
                    $actual[$attrib][$type][$key] = $page->getLabel();
                }
            }
        }

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
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
                . 'rel="' . $type . '">' . PHP_EOL
                . '<link '
                . 'href="http&#x3A;&#x2F;&#x2F;www.example.com&#x2F;" '
                . 'rev="' . $type . '">';
            $actualOutput   = $this->helper->render();

            $expected[$type] = $expectedOutput;
            $actual[$type]   = $actualOutput;

            // remove forced relation
            $active->removeRel($type);
            $active->removeRev($type);
        }

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     */
    public function testRenderFlagBitwiseOr(): void
    {
        $newFlag = Navigation\Links::RENDER_NEXT
            | Navigation\Links::RENDER_PREV;
        $this->helper->setRenderFlag($newFlag);
        $active = $this->helper->findOneByLabel('Page 1.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $active->setActive(true);

        // test data
        $expected = '<link href="page2" rel="next" title="Page&#x20;2">'
            . PHP_EOL
            . '<link href="page1" rel="prev" title="Page&#x20;1">';
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws ExceptionInterface
     */
    public function testIndenting(): void
    {
        $active = $this->helper->findOneByLabel('Page 1.1');

        self::assertInstanceOf(PageInterface::class, $active);

        $newFlag = Navigation\Links::RENDER_NEXT
            | Navigation\Links::RENDER_PREV;
        $this->helper->setRenderFlag($newFlag);
        $this->helper->setIndent('  ');
        $active->setActive(true);

        // build expected and actual result
        $expected = '  <link href="page2" rel="next" title="Page&#x20;2">'
            . PHP_EOL
            . '  <link href="page1" rel="prev" title="Page&#x20;1">';
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testSetMaxDepth(): void
    {
        $this->helper->setMaxDepth(1);
        // level 2
        $this->helper->findOneByLabel('Page 2.3.3')->setActive();
        $flag = Navigation\Links::RENDER_NEXT;

        $expected = '<link href="page2&#x2F;page2_3&#x2F;page2_3_1" rel="next" title="Page&#x20;2.3.1">';
        $actual   = $this->helper->setRenderFlag($flag)->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testSetMinDepth(): void
    {
        $this->helper->setMinDepth(2);
        // level 1
        $this->helper->findOneByLabel('Page 2.3')->setActive();
        $flag = Navigation\Links::RENDER_NEXT;

        $expected = '';
        $actual   = $this->helper->setRenderFlag($flag)->render();

        self::assertSame($expected, $actual);
    }

    /**
     * Returns the contens of the expected $file, normalizes newlines
     *
     * @throws Exception
     */
    #[Override]
    protected function getExpected(string $file): string
    {
        return str_replace("\n", PHP_EOL, parent::getExpected($file));
    }

    /**
     * @return array<int, string>
     *
     * @throws void
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
}
