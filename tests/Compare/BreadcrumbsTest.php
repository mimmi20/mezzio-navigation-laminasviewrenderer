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
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\Partial;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;

/**
 * Tests Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs.
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
final class BreadcrumbsTest extends AbstractTest
{
    /** @codingStandardsIgnoreStart */

    /**
     * Class name for view helper to test.
     *
     * @var string
     */
    protected $helperName = Breadcrumbs::class;

    /**
     * View helper.
     *
     * @var Breadcrumbs
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

        $escapeHelper = $plugin->get(EscapeHtml::class);
        \assert(
            $escapeHelper instanceof EscapeHtml,
            sprintf(
                '$escapeHelper should be an Instance of %s, but was %s',
                EscapeHtml::class,
                get_class($escapeHelper)
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

        $partialHelper = $plugin->get(Partial::class);
        \assert(
            $partialHelper instanceof Partial,
            sprintf(
                '$partialHelper should be an Instance of %s, but was %s',
                Partial::class,
                get_class($partialHelper)
            )
        );

        $translator = null;

        // create helper
        $this->helper = new Breadcrumbs(
            $this->serviceManager,
            $logger,
            $helperPluginManager->get(HtmlifyInterface::class),
            $helperPluginManager->get(ContainerParserInterface::class),
            $escapeHelper,
            $partialHelper,
            $translator
        );

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
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
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
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
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testNullOutContainer(): void
    {
        $old = $this->helper->getContainer();
        $this->helper->setContainer();
        $new = $this->helper->getContainer();

        self::assertNotSame($old, $new);
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
    public function testSetSeparator(): void
    {
        self::markTestSkipped();
//        $this->helper->setSeparator('foo');
//
//        $expected = $this->_getExpected('bc/separator.html');
//        $this->assertEquals($expected, $this->helper->render());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMaxDepth(): void
    {
        $this->helper->setMaxDepth(1);

        $expected = $this->_getExpected('bc/maxdepth.html');
        self::assertEquals($expected, $this->helper->render());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMinDepth(): void
    {
        $this->helper->setMinDepth(1);

        $expected = '';
        self::assertEquals($expected, $this->helper->render($this->nav2));
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
    public function testLinkLastElement(): void
    {
        self::markTestSkipped();
//        $this->helper->setLinkLast(true);
//
//        $expected = $this->_getExpected('bc/linklast.html');
//        $this->assertEquals($expected, $this->helper->render());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetIndent(): void
    {
        $this->helper->setIndent(8);

        $expected = '        <a';
        $actual   = mb_substr($this->helper->render(), 0, mb_strlen($expected));

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
    public function testRenderSuppliedContainerWithoutInterfering(): void
    {
        self::markTestSkipped();
//        $this->helper->setMinDepth(0);
//
//        $rendered1 = $this->_getExpected('bc/default.html');
//        $rendered2 = 'Site 2';
//
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
    public function testUseAclResourceFromPages(): void
    {
        self::markTestSkipped();
//        $acl = $this->_getAcl();
//        $this->helper->setAuthorization($acl['acl']);
//        $this->helper->setRole($acl['role']);
//
//        $expected = $this->_getExpected('bc/acl.html');
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
    public function testRenderingPartial(): void
    {
        self::markTestSkipped();
//        $this->helper->setPartial('bc.phtml');
//
//        $expected = $this->_getExpected('bc/partial.html');
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
    public function testRenderingPartialWithSeparator(): void
    {
        self::markTestSkipped();
//        $this->helper->setPartial('bc_separator.phtml')->setSeparator(' / ');
//
//        $expected = trim($this->_getExpected('bc/partialwithseparator.html'));
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
    public function testRenderingPartialBySpecifyingAnArrayAsPartial(): void
    {
        self::markTestSkipped();
//        $this->helper->setPartial(['bc.phtml', 'application']);
//
//        $expected = $this->_getExpected('bc/partial.html');
//        $this->assertEquals($expected, $this->helper->render());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testRenderingPartialShouldFailOnInvalidPartialArray(): void
    {
        $this->helper->setPartial(['bc.phtml']);

        try {
            $this->helper->render();

            self::fail(
                '$partial was invalid, but no Laminas\View\Exception\ExceptionInterface was thrown'
            );
        } catch (ExceptionInterface $e) {
        }
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
//        $this->helper->setPartial('bc_with_partial_params.phtml')->setSeparator(' / ');
//        $expected = $this->_getExpected('bc/partial_with_params.html');
//        $actual = $this->helper->renderPartialWithParams(['variable' => 'test value']);
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
    public function testLastBreadcrumbShouldBeEscaped(): void
    {
        self::markTestSkipped();
//        $container = new Navigation([
//            [
//                'label'  => 'Live & Learn',
//                'uri'    => '#',
//                'active' => true,
//            ],
//        ]);
//
//        $expected = 'Live &amp; Learn';
//        $actual = $this->helper->setMinDepth(0)->render($container);
//
//        $this->assertEquals($expected, $actual);
    }
}
