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

use Laminas\Config\Exception\InvalidArgumentException;
use Laminas\Config\Exception\RuntimeException;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface;
use Mimmi20\Mezzio\Navigation\Page\PageFactory;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;

use function assert;
use function get_debug_type;
use function is_string;
use function rtrim;
use function sprintf;
use function str_replace;
use function trim;

use const PHP_EOL;

/**
 * Tests Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu.
 */
#[Group('Compare')]
#[Group('Laminas_View')]
#[Group('Laminas_View_Helper')]
final class MenuTest extends AbstractTestCase
{
    /**
     * View helper
     *
     * @var Menu
     */
    private ViewHelperInterface $helper;

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $plugin = $this->serviceManager->get(ViewHelperPluginManager::class);
        assert($plugin instanceof ViewHelperPluginManager);

        $renderer = $this->serviceManager->get(PartialRendererInterface::class);
        assert(
            $renderer instanceof PartialRendererInterface,
            sprintf(
                '$renderer should be an Instance of %s, but was %s',
                PartialRendererInterface::class,
                get_debug_type($renderer),
            ),
        );

        $baseUrlHelper = $this->serviceManager->get(BaseServerUrlHelper::class);
        assert(
            $baseUrlHelper instanceof BaseServerUrlHelper,
            sprintf(
                '$baseUrlHelper should be an Instance of %s, but was %s',
                BaseServerUrlHelper::class,
                get_debug_type($baseUrlHelper),
            ),
        );

        $escapeHelper = $plugin->get(EscapeHtmlAttr::class);
        assert(
            $escapeHelper instanceof EscapeHtmlAttr,
            sprintf(
                '$escapeHelper should be an Instance of %s, but was %s',
                EscapeHtmlAttr::class,
                get_debug_type($escapeHelper),
            ),
        );

        $logger          = $this->serviceManager->get(LoggerInterface::class);
        $htmlify         = $this->serviceManager->get(HtmlifyInterface::class);
        $containerParser = $this->serviceManager->get(ContainerParserInterface::class);

        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);

        // create helper
        $this->helper = new Menu(
            $this->serviceManager,
            $logger,
            $htmlify,
            $containerParser,
            $escapeHelper,
            $renderer,
        );

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testCanRenderMenuFromServiceAlias(): void
    {
        $returned = $this->helper->renderMenu('Navigation');
        $expected = $this->getExpected('menu/default1.html');

        self::assertSame($expected, $returned);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testCanRenderPartialFromServiceAlias(): void
    {
        $this->helper->setPartial('test::menu');

        $returned = $this->helper->renderPartial('Navigation');
        $expected = $this->getExpected('menu/partial.html');

        self::assertSame($expected, $returned);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = ($this->helper)();
        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav1, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = ($this->helper)($this->nav2);
        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav2, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testNullingOutContainerInHelper(): void
    {
        $this->helper->setContainer();
        self::assertCount(0, $this->helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetIndentAndOverrideInRenderMenu(): void
    {
        $this->helper->setIndent(8);

        $expected = [
            'indent4' => $this->getExpected('menu/indent4.html'),
            'indent8' => $this->getExpected('menu/indent8.html'),
        ];

        $actual = [
            'indent4' => rtrim($this->helper->renderMenu(null, ['indent' => 4]), PHP_EOL),
            'indent8' => rtrim($this->helper->renderMenu(), PHP_EOL),
        ];

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderSuppliedContainerWithoutInterfering(): void
    {
        $rendered1 = $this->getExpected('menu/default1.html');
        $rendered2 = $this->getExpected('menu/default2.html');
        $expected  = [
            'registered' => $rendered1,
            'supplied' => $rendered2,
            'registered_again' => $rendered1,
        ];

        $actual = [
            'registered' => $this->helper->render(),
            'supplied' => $this->helper->render($this->nav2),
            'registered_again' => $this->helper->render(),
        ];

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testUseAclRoleAsString(): void
    {
        $acl = $this->getAcl();
        assert($acl['acl'] instanceof AuthorizationInterface);
        $this->helper->setAuthorization($acl['acl']);
        assert(is_string($acl['role']));
        $this->helper->setRole('member');

        $expected = $this->getExpected('menu/acl_string.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testFilterOutPagesBasedOnAcl(): void
    {
        $acl = $this->getAcl();
        assert($acl['acl'] instanceof AuthorizationInterface);
        $this->helper->setAuthorization($acl['acl']);
        assert(is_string($acl['role']));
        $this->helper->setRole($acl['role']);

        $expected = $this->getExpected('menu/acl.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Permissions\Acl\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testDisablingAcl(): void
    {
        $acl = $this->getAcl();
        assert($acl['acl'] instanceof AuthorizationInterface);
        $this->helper->setAuthorization($acl['acl']);
        assert(is_string($acl['role']));
        $this->helper->setRole($acl['role']);
        $this->helper->setUseAuthorization(false);

        $expected = $this->getExpected('menu/default1.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetUlCssClass(): void
    {
        $this->helper->setUlClass('My_Nav');

        $expected = $this->getExpected('menu/css.html');
        $actual   = $this->helper->render($this->nav2);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetLiActiveCssClass(): void
    {
        $this->helper->setLiActiveClass('activated');

        $expected = $this->getExpected('menu/css2.html');
        $actual   = $this->helper->render($this->nav2);

        self::assertSame(trim($expected), $actual);
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionEscapeLabelsAsTrue(): void
    {
        $options = ['escapeLabels' => true];

        $nav2 = clone $this->nav2;
        $page = (new PageFactory())->factory(
            [
                'label' => 'Badges <span class="badge">1</span>',
                'uri' => 'badges',
            ],
        );

        $nav2->addPage($page);

        $expected = $this->getExpected('menu/escapelabels_as_true.html');
        $actual   = $this->helper->renderMenu($nav2, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionEscapeLabelsAsFalse(): void
    {
        $options = ['escapeLabels' => false];

        $nav2 = clone $this->nav2;
        $page = (new PageFactory())->factory(
            [
                'label' => 'Badges <span class="badge">1</span>',
                'uri' => 'badges',
            ],
        );

        $nav2->addPage($page);

        $expected = $this->getExpected('menu/escapelabels_as_false.html');
        $actual   = $this->helper->renderMenu($nav2, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderingPartial(): void
    {
        $this->helper->setPartial('test::menu');

        $expected = $this->getExpected('menu/partial.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderingPartialBySpecifyingAnArrayAsPartial(): void
    {
        $this->helper->setPartial(['test::menu', 'application']);

        $expected = $this->getExpected('menu/partial.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testRenderingPartialWithParams(): void
    {
        $this->helper->setPartial(['test::menu-with-partials', 'application']);

        $expected = $this->getExpected('menu/partial_with_params.html');
        $actual   = $this->helper->renderPartialWithParams(['variable' => 'test value']);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws AssertionFailedError
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderingPartialShouldFailOnInvalidPartialArray(): void
    {
        $this->helper->setPartial(['menu.phtml']);

        try {
            $this->helper->render();
            self::fail('invalid $partial should throw Laminas\View\Exception\InvalidArgumentException');
        } catch (ExceptionInterface) {
        }
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetMaxDepth(): void
    {
        $this->helper->setMaxDepth(1);

        $expected = $this->getExpected('menu/maxdepth.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetMinDepth(): void
    {
        $this->helper->setMinDepth(1);

        $expected = $this->getExpected('menu/mindepth.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetBothDepts(): void
    {
        $this->helper->setMinDepth(1)->setMaxDepth(2);

        $expected = $this->getExpected('menu/bothdepts.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetOnlyActiveBranch(): void
    {
        $this->helper->setOnlyActiveBranch(true);

        $expected = $this->getExpected('menu/onlyactivebranch.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetRenderParents(): void
    {
        $this->helper->setOnlyActiveBranch(true)->setRenderParents(false);

        $expected = $this->getExpected('menu/onlyactivebranch_noparents.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetOnlyActiveBranchAndMinDepth(): void
    {
        $this->helper->setOnlyActiveBranch()->setMinDepth(1);

        $expected = $this->getExpected('menu/onlyactivebranch_mindepth.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOnlyActiveBranchAndMaxDepth(): void
    {
        $this->helper->setOnlyActiveBranch()->setMaxDepth(2);

        $expected = $this->getExpected('menu/onlyactivebranch_maxdepth.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOnlyActiveBranchAndBothDepthsSpecified(): void
    {
        $this->helper->setOnlyActiveBranch()->setMinDepth(1)->setMaxDepth(2);

        $expected = $this->getExpected('menu/onlyactivebranch_bothdepts.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOnlyActiveBranchNoParentsAndBothDepthsSpecified(): void
    {
        $this->helper->setOnlyActiveBranch()
            ->setMinDepth(1)
            ->setMaxDepth(2)
            ->setRenderParents(false);

        $expected = $this->getExpected('menu/onlyactivebranch_np_bd.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOnlyActiveBranchNoParentsActiveOneBelowMinDepth(): void
    {
        $this->setActive('Page 2');

        $this->helper->setOnlyActiveBranch()
            ->setMinDepth(1)
            ->setMaxDepth(1)
            ->setRenderParents(false);

        $expected = $this->getExpected('menu/onlyactivebranch_np_bd2.html');
        $actual   = $this->helper->renderMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderSubMenuShouldOverrideOptions(): void
    {
        $this->helper->setOnlyActiveBranch(false)
            ->setMinDepth(1)
            ->setMaxDepth(2)
            ->setRenderParents(true);

        $expected = $this->getExpected('menu/onlyactivebranch_noparents.html');
        $actual   = $this->helper->renderSubMenu();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionMaxDepth(): void
    {
        $options = ['maxDepth' => 1];

        $expected = $this->getExpected('menu/maxdepth.html');
        $actual   = $this->helper->renderMenu(null, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionMinDepth(): void
    {
        $options = ['minDepth' => 1];

        $expected = $this->getExpected('menu/mindepth.html');
        $actual   = $this->helper->renderMenu(null, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionBothDepts(): void
    {
        $options = [
            'minDepth' => 1,
            'maxDepth' => 2,
        ];

        $expected = $this->getExpected('menu/bothdepts.html');
        $actual   = $this->helper->renderMenu(null, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionOnlyActiveBranch(): void
    {
        $options = ['onlyActiveBranch' => true];

        $expected = $this->getExpected('menu/onlyactivebranch.html');
        $actual   = $this->helper->renderMenu(null, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionOnlyActiveBranchNoParents(): void
    {
        $options = [
            'onlyActiveBranch' => true,
            'renderParents' => false,
        ];

        $expected = $this->getExpected('menu/onlyactivebranch_noparents.html');
        $actual   = $this->helper->renderMenu(null, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionOnlyActiveBranchAndMinDepth(): void
    {
        $options = [
            'minDepth' => 1,
            'onlyActiveBranch' => true,
        ];

        $expected = $this->getExpected('menu/onlyactivebranch_mindepth.html');
        $actual   = $this->helper->renderMenu(null, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionOnlyActiveBranchAndMaxDepth(): void
    {
        $options = [
            'maxDepth' => 2,
            'onlyActiveBranch' => true,
        ];

        $expected = $this->getExpected('menu/onlyactivebranch_maxdepth.html');
        $actual   = $this->helper->renderMenu(null, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionOnlyActiveBranchAndBothDepthsSpecified(): void
    {
        $options = [
            'minDepth' => 1,
            'maxDepth' => 2,
            'onlyActiveBranch' => true,
        ];

        $expected = $this->getExpected('menu/onlyactivebranch_bothdepts.html');
        $actual   = $this->helper->renderMenu(null, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testOptionOnlyActiveBranchNoParentsAndBothDepthsSpecified(): void
    {
        $options = [
            'minDepth' => 2,
            'maxDepth' => 2,
            'onlyActiveBranch' => true,
            'renderParents' => false,
        ];

        $expected = $this->getExpected('menu/onlyactivebranch_np_bd.html');
        $actual   = $this->helper->renderMenu(null, $options);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderingWithoutPageClassToLi(): void
    {
        $nav2 = clone $this->nav2;
        $page = (new PageFactory())->factory(
            [
                'label' => 'Class test',
                'uri' => 'test',
                'class' => 'foobar',
            ],
        );

        $nav2->addPage($page);

        $expected = $this->getExpected('menu/addclasstolistitem_as_false.html');
        $actual   = $this->helper->renderMenu($nav2);

        self::assertSame(trim($expected), trim($actual));
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderingWithPageClassToLi(): void
    {
        $options = ['addClassToListItem' => true];

        $nav2 = clone $this->nav2;
        $page = (new PageFactory())->factory(
            [
                'label' => 'Class test',
                'uri' => 'test',
                'class' => 'foobar',
            ],
        );
        $nav2->addPage($page);

        $expected = $this->getExpected('menu/addclasstolistitem_as_true.html');
        $actual   = $this->helper->renderMenu($nav2, $options);

        self::assertSame(trim($expected), trim($actual));
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderDeepestMenuWithPageClassToLi(): void
    {
        $options = [
            'addClassToListItem' => true,
            'onlyActiveBranch' => true,
            'renderParents' => false,
        ];

        $nav2 = clone $this->nav2;

        $page = $nav2->findOneByLabel('Site 2');
        assert(
            $page instanceof PageInterface,
            sprintf(
                '$page should be an Instance of %s, but was %s',
                PageInterface::class,
                $page::class,
            ),
        );

        self::assertInstanceOf(PageInterface::class, $page);
        $page->setClass('foobar');

        $expected = $this->getExpected('menu/onlyactivebranch_addclasstolistitem.html');
        $actual   = $this->helper->renderMenu($nav2, $options);

        self::assertSame(trim($expected), trim($actual));
    }

    /**
     * Returns the contens of the expected $file, normalizes newlines.
     *
     * @throws Exception
     */
    protected function getExpected(string $file): string
    {
        return str_replace(
            ["\r\n", "\n", "\r", '##lb##'],
            ['##lb##', '##lb##', '##lb##', PHP_EOL],
            parent::getExpected($file),
        );
    }

    /** @throws void */
    private function setActive(string $label): void
    {
        $container = $this->helper->getContainer();

        foreach ($container->findAllByActive(true) as $page) {
            $page->setActive(false);
        }

        $p = $container->findOneByLabel($label);

        if (!$p) {
            return;
        }

        $p->setActive(true);
    }
}
