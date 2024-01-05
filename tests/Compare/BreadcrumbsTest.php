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
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface;
use Mimmi20\Mezzio\Navigation\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;

use function assert;
use function get_debug_type;
use function is_string;
use function mb_strlen;
use function mb_substr;
use function sprintf;
use function str_replace;
use function trim;

use const PHP_EOL;

/**
 * Tests Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs.
 */
#[Group('Compare')]
#[Group('Laminas_View')]
#[Group('Laminas_View_Helper')]
final class BreadcrumbsTest extends AbstractTestCase
{
    /**
     * View helper
     *
     * @var Breadcrumbs
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

        $escapeHelper = $plugin->get(EscapeHtml::class);
        assert(
            $escapeHelper instanceof EscapeHtml,
            sprintf(
                '$escapeHelper should be an Instance of %s, but was %s',
                EscapeHtml::class,
                get_debug_type($escapeHelper),
            ),
        );

        $translator = null;

        $logger          = $this->serviceManager->get(LoggerInterface::class);
        $htmlify         = $this->serviceManager->get(HtmlifyInterface::class);
        $containerParser = $this->serviceManager->get(ContainerParserInterface::class);

        assert($logger instanceof LoggerInterface);
        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);

        // create helper
        $this->helper = new Breadcrumbs(
            $this->serviceManager,
            $logger,
            $htmlify,
            $containerParser,
            $escapeHelper,
            $renderer,
            $translator,
        );

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);
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
    public function testNullOutContainer(): void
    {
        $old = $this->helper->getContainer();
        $this->helper->setContainer();
        $new = $this->helper->getContainer();

        self::assertNotSame($old, $new);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetSeparator(): void
    {
        $this->helper->setSeparator('foo');

        $expected = $this->getExpected('bc/separator.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
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

        $expected = $this->getExpected('bc/maxdepth.html');
        $actual   = $this->helper->render();

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

        $expected = '';
        $actual   = $this->helper->render($this->nav2);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testLinkLastElement(): void
    {
        $this->helper->setLinkLast(true);

        $expected = $this->getExpected('bc/linklast.html');
        $actual   = $this->helper->render();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testSetIndent(): void
    {
        $this->helper->setIndent(8);

        $expected = '        <a';
        $actual   = mb_substr($this->helper->render(), 0, mb_strlen($expected));

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
        $this->helper->setMinDepth(0);
        self::assertSame(0, $this->helper->getMinDepth());

        $rendered1 = $this->getExpected('bc/default.html');
        $rendered2 = 'Site 2';

        $expected = [
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
    public function testUseAclResourceFromPages(): void
    {
        $acl = $this->getAcl();
        assert($acl['acl'] instanceof AuthorizationInterface);
        $this->helper->setAuthorization($acl['acl']);
        assert(is_string($acl['role']));
        $this->helper->setRole($acl['role']);

        $expected = $this->getExpected('bc/acl.html');
        self::assertSame($expected, $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderingPartial(): void
    {
        $this->helper->setPartial('test::bc');

        $expected = $this->getExpected('bc/partial.html');
        self::assertSame($expected, $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderingPartialWithSeparator(): void
    {
        $this->helper->setPartial('test::bc-separator')->setSeparator(' / ');

        $expected = trim($this->getExpected('bc/partialwithseparator.html'));
        self::assertSame($expected, $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderingPartialBySpecifyingAnArrayAsPartial(): void
    {
        $this->helper->setPartial(['test::bc', 'application']);

        $expected = $this->getExpected('bc/partial.html');
        self::assertSame($expected, $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testRenderingPartialShouldFailOnInvalidPartialArray(): void
    {
        $this->helper->setPartial(['bc.phtml']);

        try {
            $this->helper->render();

            self::fail(
                '$partial was invalid, but no Laminas\View\Exception\ExceptionInterface was thrown',
            );
        } catch (ExceptionInterface) {
        }
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testRenderingPartialWithParams(): void
    {
        $this->helper->setPartial('test::bc-with-partials')->setSeparator(' / ');

        $expected = $this->getExpected('bc/partial_with_params.html');
        $actual   = $this->helper->renderPartialWithParams(['variable' => 'test value']);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws Exception
     * @throws \Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\I18n\Exception\RuntimeException
     */
    public function testLastBreadcrumbShouldBeEscaped(): void
    {
        $container = new Navigation();

        $page = (new PageFactory())->factory(
            [
                'label' => 'Live & Learn',
                'uri' => '#',
                'active' => true,
            ],
        );

        $container->addPage($page);

        $expected = 'Live &amp; Learn';
        $actual   = $this->helper->setMinDepth(0)->render($container);

        self::assertSame($expected, $actual);
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
}
