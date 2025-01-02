<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Compare;

use DOMDocument;
use DOMElement;
use Laminas\Uri\Uri;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\LaminasView\ServerUrlHelper;
use Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface;
use Mimmi20\Mezzio\Navigation\Page\PageFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Override;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\UriInterface;

use function assert;
use function date_default_timezone_get;
use function date_default_timezone_set;
use function get_debug_type;
use function sprintf;
use function trim;

/**
 * Tests Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
 * phpcs:disable SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
 */
#[Group('Compare')]
#[Group('Laminas_View')]
#[Group('Laminas_View_Helper')]
final class SitemapTest extends AbstractTestCase
{
    /**
     * View helper
     *
     * @var Sitemap
     */
    private ViewHelperInterface $helper;

    /** @var array<string, int|string> */
    private array $oldServer = [];

    /**
     * Stores the original set timezone
     */
    private string $originalTimezone;

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     */
    #[Override]
    protected function setUp(): void
    {
        $this->originalTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Berlin');

        if (isset($_SERVER['SERVER_NAME'])) {
            $this->oldServer['SERVER_NAME'] = $_SERVER['SERVER_NAME'];
        }

        if (isset($_SERVER['SERVER_PORT'])) {
            $this->oldServer['SERVER_PORT'] = $_SERVER['SERVER_PORT'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->oldServer['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
        }

        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['REQUEST_URI'] = '/';

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

        $uri = new class () implements UriInterface {
            private string $schema   = 'http';
            private string $host     = 'localhost';
            private int | null $port = 80;
            private string $path     = '/';
            private string $query    = '';
            private string $fragment = '';

            /**
             * @return string the URI scheme
             *
             * @throws void
             */
            #[Override]
            public function getScheme(): string
            {
                return $this->schema;
            }

            /**
             * @return string the URI authority, in "[user-info@]host[:port]" format
             *
             * @throws void
             */
            #[Override]
            public function getAuthority(): string
            {
                return '';
            }

            /**
             * @return string the URI user information, in "username[:password]" format
             *
             * @throws void
             */
            #[Override]
            public function getUserInfo(): string
            {
                return '';
            }

            /**
             * @return string the URI host
             *
             * @throws void
             */
            #[Override]
            public function getHost(): string
            {
                return $this->host;
            }

            /**
             * @return int|null the URI port
             *
             * @throws void
             */
            #[Override]
            public function getPort(): int | null
            {
                return $this->port;
            }

            /**
             * @return string the URI path
             *
             * @throws void
             */
            #[Override]
            public function getPath(): string
            {
                return $this->path;
            }

            /**
             * @return string the URI query string
             *
             * @throws void
             */
            #[Override]
            public function getQuery(): string
            {
                return $this->query;
            }

            /**
             * @return string the URI fragment
             *
             * @throws void
             */
            #[Override]
            public function getFragment(): string
            {
                return $this->fragment;
            }

            /**
             * @param string $scheme the scheme to use with the new instance
             *
             * @return static a new instance with the specified scheme
             *
             * @throws void
             */
            #[Override]
            public function withScheme(string $scheme): UriInterface
            {
                $mod         = clone $this;
                $mod->schema = $scheme;

                return $mod;
            }

            /**
             * @param string      $user     the username to use for authority
             * @param string|null $password the password associated with $user
             *
             * @return static a new instance with the specified user information
             *
             * @throws void
             *
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            #[Override]
            public function withUserInfo(string $user, string | null $password = null): UriInterface
            {
                return clone $this;
            }

            /**
             * @param string $host the hostname to use with the new instance
             *
             * @return static a new instance with the specified host
             *
             * @throws void
             */
            #[Override]
            public function withHost(string $host): UriInterface
            {
                $mod       = clone $this;
                $mod->host = $host;

                return $mod;
            }

            /**
             * @param int|null $port the port to use with the new instance; a null value
             *                       removes the port information
             *
             * @return static a new instance with the specified port
             *
             * @throws void
             */
            #[Override]
            public function withPort(int | null $port): UriInterface
            {
                $mod       = clone $this;
                $mod->port = $port;

                return $mod;
            }

            /**
             * @param string $path the path to use with the new instance
             *
             * @return static a new instance with the specified path
             *
             * @throws void
             */
            #[Override]
            public function withPath(string $path): UriInterface
            {
                $mod       = clone $this;
                $mod->path = $path;

                return $mod;
            }

            /**
             * @param string $query the query string to use with the new instance
             *
             * @return static a new instance with the specified query string
             *
             * @throws void
             */
            #[Override]
            public function withQuery(string $query): UriInterface
            {
                $mod        = clone $this;
                $mod->query = $query;

                return $mod;
            }

            /**
             * @param string $fragment the fragment to use with the new instance
             *
             * @return static a new instance with the specified fragment
             *
             * @throws void
             */
            #[Override]
            public function withFragment(string $fragment): UriInterface
            {
                $mod           = clone $this;
                $mod->fragment = $fragment;

                return $mod;
            }

            /** @throws \Laminas\Uri\Exception\InvalidArgumentException */
            #[Override]
            public function __toString(): string
            {
                $uri = new Uri();
                $uri->setScheme($this->schema);
                $uri->setHost($this->host);

                if ($this->port !== 80) {
                    $uri->setPort($this->port);
                }

                $uri->setPath($this->path);
                $uri->setQuery($this->query);
                $uri->setFragment($this->fragment);

                return $uri->toString();
            }
        };

        $baseUrlHelper->setUri($uri);

        $serverUrlHelper = $plugin->get(ServerUrlHelper::class);
        assert(
            $serverUrlHelper instanceof ServerUrlHelper,
            sprintf(
                '$serverUrlHelper should be an Instance of %s, but was %s',
                ServerUrlHelper::class,
                get_debug_type($serverUrlHelper),
            ),
        );

        $basePathHelper = $plugin->get(BasePath::class);
        assert(
            $basePathHelper instanceof BasePath,
            sprintf(
                '$basePathHelper should be an Instance of %s, but was %s',
                BasePath::class,
                get_debug_type($basePathHelper),
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

        $basePathHelper->setBasePath('');

        $htmlify         = $this->serviceManager->get(HtmlifyInterface::class);
        $containerParser = $this->serviceManager->get(ContainerParserInterface::class);

        assert($htmlify instanceof HtmlifyInterface);
        assert($containerParser instanceof ContainerParserInterface);

        // create helper
        $this->helper = new Sitemap(
            htmlify: $htmlify,
            containerParser: $containerParser,
            basePathHelper: $basePathHelper,
            escaper: $escapeHelper,
            serverUrlHelper: $serverUrlHelper,
        );

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);

        $this->helper->setFormatOutput(true);
    }

    /** @throws void */
    #[Override]
    protected function tearDown(): void
    {
        foreach ($this->oldServer as $key => $value) {
            $_SERVER[$key] = $value;
        }

        date_default_timezone_set($this->originalTimezone);
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
     * @throws InvalidArgumentException
     */
    public function testNullingOutNavigation(): void
    {
        $this->helper->setContainer();
        self::assertCount(0, $this->helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testSettingMaxDepth(): void
    {
        $this->helper->setMaxDepth(0);

        $expected = $this->getExpected('sitemap/depth1.xml');
        self::assertSame(trim($expected), $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testSettingMinDepth(): void
    {
        $this->helper->setMinDepth(1);

        $expected = $this->getExpected('sitemap/depth2.xml');
        self::assertSame(trim($expected), $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testSettingBothDepths(): void
    {
        $this->helper->setMinDepth(1);
        $this->helper->setMaxDepth(2);

        $expected = $this->getExpected('sitemap/depth3.xml');
        self::assertSame(trim($expected), $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testDropXmlDeclaration(): void
    {
        $this->helper->setUseXmlDeclaration(false);

        $expected = $this->getExpected('sitemap/nodecl.xml');
        self::assertSame(trim($expected), $this->helper->render($this->nav2));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testDisablingValidators(): void
    {
        $page = (new PageFactory())->factory(['label' => 'Invalid', 'uri' => 'http://w.']);
        $nav  = clone $this->nav2;
        $nav->addPage($page);
        $this->helper->setUseSitemapValidators(false);

        $expected = $this->getExpected('sitemap/invalid.xml');

        // using assertEqualXMLStructure to prevent differences in libxml from invalidating test
        $expectedDom = new DOMDocument();
        $receivedDom = new DOMDocument();
        $expectedDom->loadXML($expected);
        $receivedDom->loadXML($this->helper->render($nav));

        self::assertInstanceOf(DOMElement::class, $expectedDom->documentElement);
        self::assertInstanceOf(DOMElement::class, $receivedDom->documentElement);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testSetServerUrlWithSchemeAndHost(): void
    {
        $this->helper->setServerUrl('http://sub.example.org');

        $expected = $this->getExpected('sitemap/serverurl1.xml');
        self::assertSame(trim($expected), $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    #[Group('test-123')]
    public function testSetServerUrlWithSchemeAndPortAndHostAndPath(): void
    {
        $this->helper->setServerUrl('http://sub.example.org:8080/foo/');

        $expected = $this->getExpected('sitemap/serverurl2.xml');
        self::assertSame(trim($expected), $this->helper->render());
    }

    /** @throws Exception */
    public function testGetUserSchemaValidation(): void
    {
        $this->helper->setUseSchemaValidation(true);
        self::assertTrue($this->helper->getUseSchemaValidation());
        $this->helper->setUseSchemaValidation(false);
        self::assertFalse($this->helper->getUseSchemaValidation());
    }
}
