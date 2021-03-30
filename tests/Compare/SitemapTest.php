<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace MezzioTest\Navigation\LaminasView\Compare;

use DOMDocument;
use DOMElement;
use ErrorException;
use Laminas\Config\Exception\RuntimeException;
use Laminas\Log\Logger;
use Laminas\Uri\Uri;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Helper\PluginManager as HelperPluginManager;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface;
use Mezzio\Navigation\Page\PageFactory;
use PHPUnit\Framework\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\UriInterface;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function assert;
use function date_default_timezone_get;
use function date_default_timezone_set;
use function get_class;
use function sprintf;
use function trim;

/**
 * Tests Mezzio\Navigation\LaminasView\View\Helper\Navigation\Sitemap
 *
 * @group Laminas_View
 * @group Laminas_View_Helper
 * @group Compare
 */
final class SitemapTest extends AbstractTest
{
    /**
     * Class name for view helper to test
     */
    protected string $helperName = Sitemap::class;

    /**
     * View helper
     *
     * @var Sitemap
     */
    protected ViewHelperInterface $helper;

    /** @var array<string, int|string> */
    private array $oldServer = [];

    /**
     * Stores the original set timezone
     */
    private string $originalTimezone;

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws \Laminas\Config\Exception\InvalidArgumentException
     * @throws RuntimeException
     */
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

        $helperPluginManager = $this->serviceManager->get(HelperPluginManager::class);
        $plugin              = $this->serviceManager->get(ViewHelperPluginManager::class);

        $this->serviceManager->get(LaminasViewRenderer::class);

        $baseUrlHelper = $this->serviceManager->get(BaseServerUrlHelper::class);
        assert(
            $baseUrlHelper instanceof BaseServerUrlHelper,
            sprintf(
                '$baseUrlHelper should be an Instance of %s, but was %s',
                BaseServerUrlHelper::class,
                get_class($baseUrlHelper)
            )
        );

        $uri = new class() implements UriInterface {
            private string $schema = 'http';

            private string $host = 'localhost';

            private ?int $port = 80;

            private string $path = '/';

            private string $query = '';

            private string $fragment = '';

            public function getScheme(): string
            {
                return $this->schema;
            }

            public function getAuthority(): string
            {
                return '';
            }

            public function getUserInfo(): string
            {
                return '';
            }

            public function getHost(): string
            {
                return $this->host;
            }

            public function getPort(): ?int
            {
                return $this->port;
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getQuery(): string
            {
                return $this->query;
            }

            public function getFragment(): string
            {
                return $this->fragment;
            }

            /**
             * @param string $scheme the scheme to use with the new instance
             *
             * @return static a new instance with the specified scheme
             *
             * @throws \InvalidArgumentException for invalid or unsupported schemes
             * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
             */
            public function withScheme($scheme)
            {
                $mod         = clone $this;
                $mod->schema = $scheme;

                return $mod;
            }

            /**
             * @param string      $user     the user name to use for authority
             * @param string|null $password the password associated with $user
             *
             * @return static a new instance with the specified user information
             * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            public function withUserInfo($user, $password = null)
            {
                return clone $this;
            }

            /**
             * @param string $host the hostname to use with the new instance
             *
             * @return static a new instance with the specified host
             *
             * @throws \InvalidArgumentException for invalid hostnames
             * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
             */
            public function withHost($host)
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
             * @throws \InvalidArgumentException for invalid ports
             * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
             */
            public function withPort($port)
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
             * @throws \InvalidArgumentException for invalid paths
             * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
             */
            public function withPath($path)
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
             * @throws \InvalidArgumentException for invalid query strings
             * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
             */
            public function withQuery($query)
            {
                $mod        = clone $this;
                $mod->query = $query;

                return $mod;
            }

            /**
             * @param string $fragment the fragment to use with the new instance
             *
             * @return static a new instance with the specified fragment
             * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
             */
            public function withFragment($fragment)
            {
                $mod           = clone $this;
                $mod->fragment = $fragment;

                return $mod;
            }

            /**
             * @throws \Laminas\Uri\Exception\InvalidArgumentException
             */
            public function __toString(): string
            {
                $uri = new Uri();
                $uri->setScheme($this->schema);
                $uri->setHost($this->host);
                if (80 !== $this->port) {
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
                get_class($serverUrlHelper)
            )
        );

        $basePathHelper = $plugin->get(BasePath::class);
        assert(
            $basePathHelper instanceof BasePath,
            sprintf(
                '$basePathHelper should be an Instance of %s, but was %s',
                BasePath::class,
                get_class($basePathHelper)
            )
        );

        $basePathHelper->setBasePath('');

        // create helper
        $this->helper = new Sitemap(
            $this->serviceManager,
            $this->serviceManager->get(Logger::class),
            $helperPluginManager->get(HtmlifyInterface::class),
            $helperPluginManager->get(ContainerParserInterface::class),
            $basePathHelper,
            $plugin->get(EscapeHtml::class),
            $serverUrlHelper
        );

        // set nav1 in helper as default
        $this->helper->setContainer($this->nav1);

        $this->helper->setFormatOutput(true);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
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
     * @throws ExceptionInterface
     */
    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = $this->helper->__invoke();
        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav1, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = $this->helper->__invoke($this->nav2);
        self::assertSame($this->helper, $returned);
        self::assertSame($this->nav2, $returned->getContainer());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testNullingOutNavigation(): void
    {
        $this->helper->setContainer();
        self::assertCount(0, $this->helper->getContainer());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSettingMaxDepth(): void
    {
        $this->helper->setMaxDepth(0);

        $expected = $this->getExpected('sitemap/depth1.xml');
        self::assertSame(trim($expected), $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSettingMinDepth(): void
    {
        $this->helper->setMinDepth(1);

        $expected = $this->getExpected('sitemap/depth2.xml');
        self::assertSame(trim($expected), $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
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
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testDropXmlDeclaration(): void
    {
        $this->helper->setUseXmlDeclaration(false);

        $expected = $this->getExpected('sitemap/nodecl.xml');
        self::assertSame(trim($expected), $this->helper->render($this->nav2));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws ExceptionInterface
     * @throws ErrorException
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

        self::markTestIncomplete('need to wait for replacement of function "assertEqualXMLStructure"');
        //self::assertEqualXMLStructure($expectedDom->documentElement, $receivedDom->documentElement);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function testSetServerUrlWithSchemeAndHost(): void
    {
        $this->helper->setServerUrl('http://sub.example.org');

        $expected = $this->getExpected('sitemap/serverurl1.xml');
        self::assertSame(trim($expected), $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     *
     * @group test-123
     */
    public function testSetServerUrlWithSchemeAndPortAndHostAndPath(): void
    {
        $this->helper->setServerUrl('http://sub.example.org:8080/foo/');

        $expected = $this->getExpected('sitemap/serverurl2.xml');
        self::assertSame(trim($expected), $this->helper->render());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetUserSchemaValidation(): void
    {
        $this->helper->setUseSchemaValidation(true);
        self::assertTrue($this->helper->getUseSchemaValidation());
        $this->helper->setUseSchemaValidation(false);
        self::assertFalse($this->helper->getUseSchemaValidation());
    }
}
