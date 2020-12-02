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

use Laminas\Log\Logger;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Helper\EscapeHtmlAttr;
use Laminas\View\Helper\Partial;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu;

/**
 * Tests Mezzio\Navigation\LaminasView\View\Helper\Navigation\Menu.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
final class MenuTest extends AbstractTest
{
    /** @codingStandardsIgnoreStart */

    /**
     * Class name for view helper to test.
     *
     * @var string
     */
    protected $helperName = Menu::class;

    /**
     * View helper.
     *
     * @var Menu
     */
    protected $helper;

    /** @codingStandardsIgnoreEnd */

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Laminas\Config\Exception\InvalidArgumentException
     * @throws \Laminas\Config\Exception\RuntimeException
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

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

        $partialHelper = $plugin->get(Partial::class);
        \assert(
            $partialHelper instanceof Partial,
            sprintf(
                '$partialHelper should be an Instance of %s, but was %s',
                Partial::class,
                get_class($partialHelper)
            )
        );

        // create helper
        $this->helper = new Menu(
            $this->serviceManager,
            $logger,
            $helperPluginManager->get(HtmlifyInterface::class),
            $helperPluginManager->get(ContainerParserInterface::class),
            $plugin->get(EscapeHtmlAttr::class),
            $partialHelper
        );

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);
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
    public function testCanRenderMenuFromServiceAlias(): void
    {
        self::markTestSkipped();
//        $returned = $this->helper->renderMenu('Navigation');
//        $this->assertEquals($this->_getExpected('menu/default1.html'), $returned);
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
    public function testCanRenderPartialFromServiceAlias(): void
    {
        self::markTestSkipped();
//        $this->helper->setPartial('menu.phtml');
//        $returned = $this->helper->renderPartial('Navigation');
//        $this->assertEquals($this->_getExpected('menu/partial.html'), $returned);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
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
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
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
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testNullingOutContainerInHelper(): void
    {
        $this->helper->setContainer();
        self::assertCount(0, $this->helper->getContainer());
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
    public function testSetIndentAndOverrideInRenderMenu(): void
    {
        self::markTestSkipped();
//        $this->helper->setIndent(8);
//
//        $expected = [
//            'indent4' => $this->_getExpected('menu/indent4.html'),
//            'indent8' => $this->_getExpected('menu/indent8.html'),
//        ];
//
//        $renderOptions = [
//            'indent' => 4,
//        ];
//
//        $actual = [
//            'indent4' => rtrim($this->helper->renderMenu(null, $renderOptions), PHP_EOL),
//            'indent8' => rtrim($this->helper->renderMenu(), PHP_EOL),
//        ];
//
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
    public function testRenderSuppliedContainerWithoutInterfering(): void
    {
        self::markTestSkipped();
//        $rendered1 = $this->_getExpected('menu/default1.html');
//        $rendered2 = $this->_getExpected('menu/default2.html');
//        $expected = [
//            'registered'       => $rendered1,
//            'supplied'         => $rendered2,
//            'registered_again' => $rendered1,
//        ];
//
//        $actual = [
//            'registered'       => $this->helper->render(),
//            'supplied'         => $this->helper->render($this->nav2),
//            'registered_again' => $this->helper->render(),
//        ];
//
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
    public function testUseAclRoleAsString(): void
    {
        self::markTestSkipped();
//        $acl = $this->_getAcl();
//        $this->helper->setAuthorization($acl['acl']);
//        $this->helper->setRole('member');
//
//        $expected = $this->_getExpected('menu/acl_string.html');
//        $this->assertEquals($expected, $this->helper->render());
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
    public function testFilterOutPagesBasedOnAcl(): void
    {
        self::markTestSkipped();
//        $acl = $this->_getAcl();
//        $this->helper->setAuthorization($acl['acl']);
//        $this->helper->setRole($acl['role']);
//
//        $expected = $this->_getExpected('menu/acl.html');
//        $actual = $this->helper->render();
//
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
    public function testDisablingAcl(): void
    {
        self::markTestSkipped();
//        $acl = $this->_getAcl();
//        $this->helper->setAuthorization($acl['acl']);
//        $this->helper->setRole($acl['role']);
//        $this->helper->setUseAuthorization(false);
//
//        $expected = $this->_getExpected('menu/default1.html');
//        $actual = $this->helper->render();
//
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
    public function testUseAnAclRoleInstanceFromAclObject(): void
    {
        self::markTestSkipped();
//        $acl = $this->_getAcl();
//        $this->helper->setAuthorization($acl['acl']);
//        $this->helper->setRole($acl['acl']->getRole('member'));
//
//        $expected = $this->_getExpected('menu/acl_role_interface.html');
//        $this->assertEquals($expected, $this->helper->render());
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
    public function testUseConstructedAclRolesNotFromAclObject(): void
    {
        self::markTestSkipped();
//        $acl = $this->_getAcl();
//        $this->helper->setAuthorization($acl['acl']);
//        $this->helper->setRole(new \Laminas\Permissions\Acl\Role\GenericRole('member'));
//
//        $expected = $this->_getExpected('menu/acl_role_interface.html');
//        $this->assertEquals($expected, $this->helper->render());
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
    public function testSetUlCssClass(): void
    {
        self::markTestSkipped();
//        $this->helper->setUlClass('My_Nav');
//        $expected = $this->_getExpected('menu/css.html');
//        $this->assertEquals($expected, $this->helper->render($this->nav2));
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
    public function testSetLiActiveCssClass(): void
    {
        self::markTestSkipped();
//        $this->helper->setLiActiveClass('activated');
//        $expected = $this->_getExpected('menu/css2.html');
//        $this->assertEquals(trim($expected), $this->helper->render($this->nav2));
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
    public function testOptionEscapeLabelsAsTrue(): void
    {
        self::markTestSkipped();
//        $options = [
//            'escapeLabels' => true,
//        ];
//
//        $container = new Navigation($this->nav2->toArray());
//        $page = (new PageFactory())->factory(
//            [
//                'label' => 'Badges <span class="badge">1</span>',
//                'uri' => 'badges',
//            ]
//        );
//
//        $container->addPage($page);
//
//        $expected = $this->_getExpected('menu/escapelabels_as_true.html');
//        $actual = $this->helper->renderMenu($container, $options);
//
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
    public function testOptionEscapeLabelsAsFalse(): void
    {
        self::markTestSkipped();
//        $options = [
//            'escapeLabels' => false,
//        ];
//
//        $container = new Navigation($this->nav2->toArray());
//
//        $page = (new PageFactory())->factory(
//            [
//                'label' => 'Badges <span class="badge">1</span>',
//                'uri' => 'badges',
//            ]
//        );
//
//        $container->addPage($page);
//
//        $expected = $this->_getExpected('menu/escapelabels_as_false.html');
//        $actual = $this->helper->renderMenu($container, $options);
//
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
    public function testRenderingPartial(): void
    {
        self::markTestSkipped();
//        $this->helper->setPartial('menu.phtml');
//
//        $expected = $this->_getExpected('menu/partial.html');
//        $actual = $this->helper->render();
//
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
    public function testRenderingPartialBySpecifyingAnArrayAsPartial(): void
    {
        self::markTestSkipped();
//        $this->helper->setPartial(['menu.phtml', 'application']);
//
//        $expected = $this->_getExpected('menu/partial.html');
//        $actual = $this->helper->render();
//
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
    public function testRenderingPartialWithParams(): void
    {
        self::markTestSkipped();
//        $this->helper->setPartial(['menu_with_partial_params.phtml', 'application']);
//        $expected = $this->_getExpected('menu/partial_with_params.html');
//        $actual = $this->helper->renderPartialWithParams(['variable' => 'test value']);
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \PHPUnit\Framework\AssertionFailedError
     *
     * @return void
     */
    public function testRenderingPartialShouldFailOnInvalidPartialArray(): void
    {
        $this->helper->setPartial(['menu.phtml']);

        try {
            $this->helper->render();
            self::fail('invalid $partial should throw Laminas\View\Exception\InvalidArgumentException');
        } catch (ExceptionInterface $e) {
        }
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMaxDepth(): void
    {
        $this->helper->setMaxDepth(1);

        $expected = $this->_getExpected('menu/maxdepth.html');
        $actual   = $this->helper->renderMenu();

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMinDepth(): void
    {
        $this->helper->setMinDepth(1);

        $expected = $this->_getExpected('menu/mindepth.html');
        $actual   = $this->helper->renderMenu();

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetBothDepts(): void
    {
        $this->helper->setMinDepth(1)->setMaxDepth(2);

        $expected = $this->_getExpected('menu/bothdepts.html');
        $actual   = $this->helper->renderMenu();

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetOnlyActiveBranch(): void
    {
        $this->helper->setOnlyActiveBranch(true);

        $expected = $this->_getExpected('menu/onlyactivebranch.html');
        $actual   = $this->helper->renderMenu();

        self::assertEquals($expected, $actual);
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
    public function testSetRenderParents(): void
    {
        self::markTestSkipped();
//        $this->helper->setOnlyActiveBranch(true)->setRenderParents(false);
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_noparents.html');
//        $actual   = $this->helper->renderMenu();
//
//        self::assertEquals($expected, $actual);
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
    public function testSetOnlyActiveBranchAndMinDepth(): void
    {
        self::markTestSkipped();
//        $this->helper->setOnlyActiveBranch()->setMinDepth(1);
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_mindepth.html');
//        $actual = $this->helper->renderMenu();
//
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
    public function testOnlyActiveBranchAndMaxDepth(): void
    {
        self::markTestSkipped();
//        $this->helper->setOnlyActiveBranch()->setMaxDepth(2);
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_maxdepth.html');
//        $actual = $this->helper->renderMenu();
//
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
    public function testOnlyActiveBranchAndBothDepthsSpecified(): void
    {
        self::markTestSkipped();
//        $this->helper->setOnlyActiveBranch()->setMinDepth(1)->setMaxDepth(2);
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_bothdepts.html');
//        $actual = $this->helper->renderMenu();
//
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
    public function testOnlyActiveBranchNoParentsAndBothDepthsSpecified(): void
    {
        self::markTestSkipped();
//        $this->helper->setOnlyActiveBranch()
//                      ->setMinDepth(1)
//                      ->setMaxDepth(2)
//                      ->setRenderParents(false);
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_np_bd.html');
//        $actual = $this->helper->renderMenu();
//
//        $this->assertEquals($expected, $actual);
    }

    /** @codingStandardsIgnoreStart */

    /**
     * @param string $label
     *
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    private function _setActive(string $label): void
    {
        /** @codingStandardsIgnoreEnd */
        $container = $this->helper->getContainer();

        foreach ($container->findAllByActive(true) as $page) {
            $page->setActive(false);
        }

        if (!$p = $container->findOneByLabel($label)) {
            return;
        }

        $p->setActive(true);
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
    public function testOnlyActiveBranchNoParentsActiveOneBelowMinDepth(): void
    {
        self::markTestSkipped();
//        $this->_setActive('Page 2');
//
//        $this->helper->setOnlyActiveBranch()
//                      ->setMinDepth(1)
//                      ->setMaxDepth(1)
//                      ->setRenderParents(false);
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_np_bd2.html');
//        $actual = $this->helper->renderMenu();
//
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
    public function testRenderSubMenuShouldOverrideOptions(): void
    {
        self::markTestSkipped();
//        $this->helper->setOnlyActiveBranch(false)
//                      ->setMinDepth(1)
//                      ->setMaxDepth(2)
//                      ->setRenderParents(true);
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_noparents.html');
//        $actual = $this->helper->renderSubMenu();
//
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
    public function testOptionMaxDepth(): void
    {
        self::markTestSkipped();
//        $options = [
//            'maxDepth' => 1,
//        ];
//
//        $expected = $this->_getExpected('menu/maxdepth.html');
//        $actual = $this->helper->renderMenu(null, $options);
//
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
    public function testOptionMinDepth(): void
    {
        self::markTestSkipped();
//        $options = [
//            'minDepth' => 1,
//        ];
//
//        $expected = $this->_getExpected('menu/mindepth.html');
//        $actual = $this->helper->renderMenu(null, $options);
//
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
    public function testOptionBothDepts(): void
    {
        self::markTestSkipped();
//        $options = [
//            'minDepth' => 1,
//            'maxDepth' => 2,
//        ];
//
//        $expected = $this->_getExpected('menu/bothdepts.html');
//        $actual = $this->helper->renderMenu(null, $options);
//
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
    public function testOptionOnlyActiveBranch(): void
    {
        self::markTestSkipped();
//        $options = [
//            'onlyActiveBranch' => true,
//        ];
//
//        $expected = $this->_getExpected('menu/onlyactivebranch.html');
//        $actual = $this->helper->renderMenu(null, $options);
//
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
    public function testOptionOnlyActiveBranchNoParents(): void
    {
        self::markTestSkipped();
//        $options = [
//            'onlyActiveBranch' => true,
//            'renderParents' => false,
//        ];
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_noparents.html');
//        $actual = $this->helper->renderMenu(null, $options);
//
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
    public function testOptionOnlyActiveBranchAndMinDepth(): void
    {
        self::markTestSkipped();
//        $options = [
//            'minDepth' => 1,
//            'onlyActiveBranch' => true,
//        ];
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_mindepth.html');
//        $actual = $this->helper->renderMenu(null, $options);
//
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
    public function testOptionOnlyActiveBranchAndMaxDepth(): void
    {
        self::markTestSkipped();
//        $options = [
//            'maxDepth' => 2,
//            'onlyActiveBranch' => true,
//        ];
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_maxdepth.html');
//        $actual = $this->helper->renderMenu(null, $options);
//
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
    public function testOptionOnlyActiveBranchAndBothDepthsSpecified(): void
    {
        self::markTestSkipped();
//        $options = [
//            'minDepth' => 1,
//            'maxDepth' => 2,
//            'onlyActiveBranch' => true,
//        ];
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_bothdepts.html');
//        $actual = $this->helper->renderMenu(null, $options);
//
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
    public function testOptionOnlyActiveBranchNoParentsAndBothDepthsSpecified(): void
    {
        self::markTestSkipped();
//        $options = [
//            'minDepth' => 2,
//            'maxDepth' => 2,
//            'onlyActiveBranch' => true,
//            'renderParents' => false,
//        ];
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_np_bd.html');
//        $actual = $this->helper->renderMenu(null, $options);
//
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
    public function testRenderingWithoutPageClassToLi(): void
    {
        self::markTestSkipped();
//        $container = new Navigation($this->nav2->toArray());
//        $container->addPage([
//            'label' => 'Class test',
//            'uri' => 'test',
//            'class' => 'foobar',
//        ]);
//
//        $expected = $this->_getExpected('menu/addclasstolistitem_as_false.html');
//        $actual   = $this->helper->renderMenu($container);
//
//        $this->assertEquals(trim($expected), trim($actual));
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
    public function testRenderingWithPageClassToLi(): void
    {
        self::markTestSkipped();
//        $options = [
//            'addClassToListItem' => true,
//        ];
//
//        $container = new Navigation($this->nav2->toArray());
//
//        $page = (new PageFactory())->factory(
//            [
//                'label' => 'Class test',
//                'uri' => 'test',
//                'class' => 'foobar',
//            ]
//        );
//        $container->addPage($page);
//
//        $expected = $this->_getExpected('menu/addclasstolistitem_as_true.html');
//        $actual = $this->helper->renderMenu($container, $options);
//
//        $this->assertEquals(trim($expected), trim($actual));
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
    public function testRenderDeepestMenuWithPageClassToLi(): void
    {
        self::markTestSkipped();
//        $options = [
//            'addClassToListItem' => true,
//            'onlyActiveBranch' => true,
//            'renderParents' => false,
//        ];
//
//        $pages = $this->nav2->toArray();
//        $pages[1]['class'] = 'foobar';
//        $container = new Navigation($pages);
//
//        $expected = $this->_getExpected('menu/onlyactivebranch_addclasstolistitem.html');
//        $actual = $this->helper->renderMenu($container, $options);
//
//        $this->assertEquals(trim($expected), trim($actual));
    }

    /** @codingStandardsIgnoreStart */

    /**
     * Returns the contens of the expected $file, normalizes newlines.
     *
     * @param string $file
     *
     * @return string
     */
    protected function _getExpected(string $file): string
    {
        // @codingStandardsIgnoreEnd
        return str_replace("\n", PHP_EOL, parent::_getExpected($file));
    }
}
