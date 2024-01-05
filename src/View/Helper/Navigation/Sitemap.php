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

namespace Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use DOMDocument;
use DOMException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Uri;
use Laminas\Uri\Exception\InvalidArgumentException;
use Laminas\Uri\Exception\InvalidUriException;
use Laminas\Uri\Exception\InvalidUriPartException;
use Laminas\Uri\UriInterface;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\Sitemap\Changefreq;
use Laminas\Validator\Sitemap\Lastmod;
use Laminas\Validator\Sitemap\Loc;
use Laminas\Validator\Sitemap\Priority;
use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\EscapeHtml;
use Mezzio\LaminasView\ServerUrlHelper;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Log\LoggerInterface;
use RecursiveIteratorIterator;

use function assert;
use function date;
use function get_debug_type;
use function implode;
use function in_array;
use function is_int;
use function is_string;
use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function mb_substr;
use function preg_match;
use function rtrim;
use function sprintf;
use function strtotime;
use function trim;

use const LIBXML_ERR_ERROR;
use const LIBXML_ERR_FATAL;
use const LIBXML_ERR_WARNING;
use const PHP_EOL;

/**
 * Helper for printing sitemaps
 *
 * @see http://www.sitemaps.org/protocol.php
 */
final class Sitemap extends AbstractHtmlElement implements SitemapInterface
{
    use HelperTrait;

    /**
     * Whether XML output should be formatted
     */
    private bool $formatOutput = false;

    /**
     * Server url
     */
    private string | null $serverUrl = null;

    /**
     * List of urls in the sitemap
     *
     * @var array<int, string>
     */
    private array $urls = [];

    /**
     * Whether sitemap should be validated using Laminas\Validate\Sitemap\*
     */
    private bool $useSitemapValidators = true;

    /**
     * Whether sitemap should be schema validated when generated
     */
    private bool $useSchemaValidation = false;

    /**
     * Whether the XML declaration should be included in XML output
     */
    private bool $useXmlDeclaration = true;
    private DOMDocument $dom;
    private Loc $locValidator;
    private Lastmod $lastmodValidator;
    private Priority $priorityValidator;
    private Changefreq $changefreqValidator;

    /** @throws void */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        LoggerInterface $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        private readonly BasePath $basePathHelper,
        private readonly EscapeHtml $escaper,
        private readonly ServerUrlHelper $serverUrlHelper,
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;

        libxml_use_internal_errors(true);

        $this->dom                 = new DOMDocument('1.0', 'UTF-8');
        $this->locValidator        = new Loc();
        $this->lastmodValidator    = new Lastmod();
        $this->priorityValidator   = new Priority();
        $this->changefreqValidator = new Changefreq();
    }

    /** @throws void */
    public function __destruct()
    {
        libxml_clear_errors();
    }

    /**
     * Renders helper
     *
     * Implements {@link ViewHelperInterface::render()}.
     *
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to render.
     *                                                  Default is null, which indicates
     *                                                  that the helper should render
     *                                                  the container returned by {@link getContainer()}.
     *
     * @throws Exception\RuntimeException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     */
    public function render(ContainerInterface | string | null $container = null): string
    {
        try {
            $dom = $this->getDomSitemap($container);
        } catch (DOMException $e) {
            $this->logger->error($e);

            return '';
        }

        $xml = $this->getUseXmlDeclaration()
            ? $dom->saveXML()
            : $dom->saveXML($dom->documentElement);

        return rtrim((string) $xml, PHP_EOL);
    }

    /**
     * Returns a DOMDocument containing the Sitemap XML for the given container
     *
     * @param ContainerInterface<PageInterface>|string|null $container [optional] container to get
     *                                                  sitemaps from, defaults
     *                                                  to what is registered in the
     *                                                  helper
     * @param int|null                                      $minDepth  [optional] minimum depth
     *                                                                 required for page to be
     *                                                                 valid. Default is to use
     *                                                                 {@link getMinDepth()}. A
     *                                                                 null value means no minimum
     *                                                                 depth required.
     * @param int|null                                      $maxDepth  [optional] maximum depth
     *                                                                 a page can have to be
     *                                                                 valid. Default is to use
     *                                                                 {@link getMaxDepth()}. A
     *                                                                 null value means no maximum
     *                                                                 depth required.
     *
     * @return DOMDocument DOM representation of the container
     *
     * @throws Exception\RuntimeException                         if schema validation is on
     *                                                            and the sitemap is invalid
     *                                                            according to the sitemap
     *                                                            schema, or if sitemap
     *                                                            validators are used and the
     *                                                            loc element fails validation
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws DOMException
     */
    public function getDomSitemap(
        ContainerInterface | string | null $container = null,
        int | null $minDepth = null,
        int | null $maxDepth = -1,
    ): DOMDocument {
        // Reset the urls
        $this->urls = [];

        $container = $this->containerParser->parseContainer($container);

        if ($container === null) {
            $container = $this->getContainer();
        }

        // create document
        $dom               = $this->getDom();
        $dom->formatOutput = $this->getFormatOutput();

        // ...and urlset (root) element
        $urlSet = $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'urlset');
        $dom->appendChild($urlSet);

        // create iterator
        assert($container instanceof ContainerInterface);
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

        if (!is_int($minDepth)) {
            $minDepth = $this->getMinDepth();
        }

        if ((!is_int($maxDepth) || 0 > $maxDepth) && $maxDepth !== null) {
            $maxDepth = $this->getMaxDepth();
        }

        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        foreach ($iterator as $page) {
            assert(
                $page instanceof PageInterface,
                sprintf(
                    '$page should be an Instance of %s, but was %s',
                    PageInterface::class,
                    get_debug_type($page),
                ),
            );

            $currDepth = $iterator->getDepth();

            if ($currDepth < $minDepth || !$this->accept($page)) {
                // page should not be included
                continue;
            }

            $url = $this->url($page);

            // get absolute url from page
            if (!$url) {
                // skip page if it has no url (rare case)
                // or already is in the sitemap
                continue;
            }

            // create url node for this page
            $urlNode = $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'url');
            $urlSet->appendChild($urlNode);

            if ($this->getUseSitemapValidators()) {
                $locValidator = $this->getLocValidator();

                try {
                    $isValid = $locValidator->isValid($url);
                } catch (RuntimeException $e) {
                    throw new Exception\RuntimeException(
                        sprintf(
                            'An error occured while validating an URL for Sitemap XML: "%s"',
                            $url,
                        ),
                        0,
                        $e,
                    );
                }

                if (!$isValid) {
                    throw new Exception\RuntimeException(
                        sprintf(
                            'Encountered an invalid URL for Sitemap XML: "%s"',
                            $url,
                        ),
                    );
                }
            }

            // put url in 'loc' element
            $urlNode->appendChild($dom->createElementNS(SitemapInterface::SITEMAP_NS, 'loc', $url));

            // add 'lastmod' element if a valid lastmod is set in page
            if (isset($page->lastmod)) {
                $lastmod = strtotime((string) $page->lastmod);

                // prevent 1970-01-01...
                if ($lastmod !== false) {
                    $lastmod = date('c', $lastmod);
                }

                $lastmodValidator = $this->getLastmodValidator();
                $isValid          = false;

                if ($lastmod !== false) {
                    try {
                        $isValid = $lastmodValidator->isValid($lastmod);
                    } catch (RuntimeException $e) {
                        $this->logger->error($e);
                    }
                }

                if (!$this->getUseSitemapValidators() || ($lastmod !== false && $isValid)) {
                    // Cast $lastmod to string in case no validation was used
                    $urlNode->appendChild(
                        $dom->createElementNS(
                            SitemapInterface::SITEMAP_NS,
                            'lastmod',
                            (string) $lastmod,
                        ),
                    );
                }
            }

            // add 'changefreq' element if a valid changefreq is set in page
            if (isset($page->changefreq)) {
                $changefreq          = $page->changefreq;
                $changefreqValidator = $this->getChangefreqValidator();

                try {
                    $isValid = $changefreqValidator->isValid($changefreq);
                } catch (RuntimeException $e) {
                    $this->logger->error($e);

                    $isValid = false;
                }

                if (!$this->getUseSitemapValidators() || $isValid) {
                    $urlNode->appendChild(
                        $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'changefreq', $changefreq),
                    );
                }
            }

            // add 'priority' element if a valid priority is set in page
            if (!isset($page->priority)) {
                continue;
            }

            $priority = (string) $page->priority;

            if ($this->getUseSitemapValidators()) {
                $priorityValidator = $this->getPriorityValidator();

                try {
                    $isValid = $priorityValidator->isValid($priority);
                } catch (RuntimeException $e) {
                    $this->logger->error($e);

                    continue;
                }

                if (!$isValid) {
                    continue;
                }
            }

            $urlNode->appendChild(
                $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'priority', $priority),
            );
        }

        // validate using schema if specified
        if ($this->getUseSchemaValidation()) {
            $dom->schemaValidate(SitemapInterface::SITEMAP_XSD);

            $errors = libxml_get_errors();

            /** @codeCoverageIgnoreStart */

            $validationMessages = [];

            foreach ($errors as $error) {
                $message = match ($error->level) {
                    LIBXML_ERR_FATAL => sprintf('FATAL ERROR [%s]', $error->code),
                    LIBXML_ERR_ERROR => sprintf('ERROR [%s]', $error->code),
                    LIBXML_ERR_WARNING => sprintf('WARNING [%s]', $error->code),
                    default => sprintf('NOTICE [%s]', $error->code),
                };

                $message .= trim($error->message) . sprintf(
                    ' Line: %d Column: %d',
                    $error->line,
                    $error->column,
                );

                $validationMessages[] = $message;
            }

            if ($validationMessages !== []) {
                throw new Exception\RuntimeException(
                    sprintf(
                        'Sitemap is invalid according to XML Schema at "%s": %s',
                        SitemapInterface::SITEMAP_XSD,
                        implode(' ', $validationMessages),
                    ),
                );
            }
            // @codeCoverageIgnoreEnd
        }

        return $dom;
    }

    /**
     * Returns an escaped absolute URL for the given page
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\RuntimeException
     */
    public function url(PageInterface $page): string
    {
        $href = $page->getHref();

        if ($href === '') {
            // no href
            return '';
        }

        if (mb_substr($href, 0, 1) === '/') {
            // href is relative to root; use serverUrl helper
            $url = $this->getServerUrl() . $href;
        } elseif (preg_match('/^[a-z]+:/im', $href)) {
            // scheme is given in href; assume absolute URL already
            $url = $href;
        } else {
            // href is relative to current document; use url helpers
            $curDoc = ($this->basePathHelper)();
            $curDoc = $curDoc === '/' ? '' : trim($curDoc, '/');
            $url    = rtrim(
                $this->getServerUrl(),
                '/',
            ) . '/' . $curDoc . ($curDoc === '' ? '' : '/') . $href;
        }

        if (!in_array($url, $this->urls, true)) {
            $this->urls[] = $url;

            return $this->xmlEscape($url);
        }

        return '';
    }

    /**
     * Sets whether XML output should be formatted
     *
     * @throws void
     */
    public function setFormatOutput(bool $formatOutput = true): self
    {
        $this->formatOutput = $formatOutput;

        return $this;
    }

    /**
     * Returns whether XML output should be formatted
     *
     * @throws void
     */
    public function getFormatOutput(): bool
    {
        return $this->formatOutput;
    }

    /**
     * Sets server url (scheme and host-related stuff without request URI)
     *
     * E.g. http://www.example.com
     *
     * @param string|UriInterface $uri
     *
     * @throws Exception\InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function setServerUrl($uri): self
    {
        if (is_string($uri)) {
            try {
                $uri = Uri\UriFactory::factory($uri);
            } catch (InvalidArgumentException $e) {
                throw new Exception\InvalidArgumentException('Invalid server URL', 0, $e);
            }
        }

        if (!$uri instanceof UriInterface) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '$serverUrl should be aa string or an Instance of %s',
                    UriInterface::class,
                ),
            );
        }

        try {
            $uri->setFragment('');
        } catch (InvalidUriPartException $e) {
            throw new Exception\InvalidArgumentException('Invalid server URL', 0, $e);
        }

        $uri->setPath('');
        $uri->setQuery('');

        if (!$uri->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid server URL');
        }

        try {
            $this->serverUrl = $uri->toString();
        } catch (InvalidUriException $e) {
            throw new Exception\InvalidArgumentException('Invalid server URL', 0, $e);
        }

        return $this;
    }

    /**
     * Returns server URL
     *
     * @throws void
     */
    public function getServerUrl(): string
    {
        if ($this->serverUrl === null) {
            $this->serverUrl = ($this->serverUrlHelper)();
        }

        return $this->serverUrl;
    }

    /**
     * Sets whether sitemap should be validated using Laminas\Validate\Sitemap_*
     *
     * @throws void
     */
    public function setUseSitemapValidators(bool $useSitemapValidators): self
    {
        $this->useSitemapValidators = $useSitemapValidators;

        return $this;
    }

    /**
     * Returns whether sitemap should be validated using Laminas\Validate\Sitemap_*
     *
     * @throws void
     */
    public function getUseSitemapValidators(): bool
    {
        return $this->useSitemapValidators;
    }

    /**
     * Sets whether sitemap should be schema validated when generated
     *
     * @throws void
     */
    public function setUseSchemaValidation(bool $schemaValidation): self
    {
        $this->useSchemaValidation = $schemaValidation;

        return $this;
    }

    /**
     * Returns true if sitemap should be schema validated when generated
     *
     * @throws void
     */
    public function getUseSchemaValidation(): bool
    {
        return $this->useSchemaValidation;
    }

    /**
     * Sets whether the XML declaration should be used in output
     *
     * @throws void
     */
    public function setUseXmlDeclaration(bool $useXmlDecl): self
    {
        $this->useXmlDeclaration = $useXmlDecl;

        return $this;
    }

    /**
     * Returns whether the XML declaration should be used in output
     *
     * @throws void
     */
    public function getUseXmlDeclaration(): bool
    {
        return $this->useXmlDeclaration;
    }

    /** @throws void */
    public function getDom(): DOMDocument
    {
        return $this->dom;
    }

    /**
     * @return $this
     *
     * @throws void
     */
    public function setDom(DOMDocument $dom): self
    {
        $this->dom = $dom;

        return $this;
    }

    /** @throws void */
    public function getLocValidator(): Loc
    {
        return $this->locValidator;
    }

    /**
     * @return $this
     *
     * @throws void
     */
    public function setLocValidator(Loc $locValidator): self
    {
        $this->locValidator = $locValidator;

        return $this;
    }

    /** @throws void */
    public function getLastmodValidator(): Lastmod
    {
        return $this->lastmodValidator;
    }

    /**
     * @return $this
     *
     * @throws void
     */
    public function setLastmodValidator(Lastmod $lastmodValidator): self
    {
        $this->lastmodValidator = $lastmodValidator;

        return $this;
    }

    /** @throws void */
    public function getPriorityValidator(): Priority
    {
        return $this->priorityValidator;
    }

    /**
     * @return $this
     *
     * @throws void
     */
    public function setPriorityValidator(Priority $priorityValidator): self
    {
        $this->priorityValidator = $priorityValidator;

        return $this;
    }

    /** @throws void */
    public function getChangefreqValidator(): Changefreq
    {
        return $this->changefreqValidator;
    }

    /**
     * @return $this
     *
     * @throws void
     */
    public function setChangefreqValidator(Changefreq $changefreqValidator): self
    {
        $this->changefreqValidator = $changefreqValidator;

        return $this;
    }

    /**
     * Escapes string for XML usage
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     */
    private function xmlEscape(string $string): string
    {
        $escaped = ($this->escaper)($string);
        assert(is_string($escaped));

        return $escaped;
    }
}
