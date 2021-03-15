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
namespace Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Laminas\Log\Logger;
use Laminas\Stdlib\ErrorHandler;
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
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Helper\ContainerParserInterface;
use Mezzio\Navigation\Helper\HtmlifyInterface;
use Mezzio\Navigation\Page\PageInterface;
use RecursiveIteratorIterator;

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
     *
     * @var bool
     */
    private $formatOutput = false;

    /**
     * Server url
     *
     * @var string|null
     */
    private $serverUrl;

    /**
     * List of urls in the sitemap
     *
     * @var array
     */
    private $urls = [];

    /**
     * Whether sitemap should be validated using Laminas\Validate\Sitemap\*
     *
     * @var bool
     */
    private $useSitemapValidators = true;

    /**
     * Whether sitemap should be schema validated when generated
     *
     * @var bool
     */
    private $useSchemaValidation = false;

    /**
     * Whether the XML declaration should be included in XML output
     *
     * @var bool
     */
    private $useXmlDeclaration = true;

    /** @var \Laminas\View\Helper\BasePath */
    private $basePathHelper;

    /** @var \Laminas\View\Helper\EscapeHtml */
    private $escaper;

    /** @var \Mezzio\LaminasView\ServerUrlHelper */
    private $serverUrlHelper;

    /** @var \DOMDocument */
    private $dom;

    /** @var Loc */
    private $locValidator;

    /** @var Lastmod */
    private $lastmodValidator;

    /** @var Priority */
    private $priorityValidator;

    /** @var Changefreq */
    private $changefreqValidator;

    /**
     * @param \Interop\Container\ContainerInterface $serviceLocator
     * @param Logger                                $logger
     * @param HtmlifyInterface                      $htmlify
     * @param ContainerParserInterface              $containerParser
     * @param BasePath                              $basePathHelper
     * @param EscapeHtml                            $escaper
     * @param ServerUrlHelper                       $serverUrlHelper
     */
    public function __construct(
        \Interop\Container\ContainerInterface $serviceLocator,
        Logger $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        BasePath $basePathHelper,
        EscapeHtml $escaper,
        ServerUrlHelper $serverUrlHelper
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;
        $this->basePathHelper  = $basePathHelper;
        $this->escaper         = $escaper;
        $this->serverUrlHelper = $serverUrlHelper;
    }

    /**
     * Renders helper
     *
     * Implements {@link ViewHelperInterface::render()}.
     *
     * @param ContainerInterface|string|null $container [optional] container to render.
     *                                                  Default is null, which indicates
     *                                                  that the helper should render
     *                                                  the container returned by {@link getContainer()}.
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     *
     * @return string
     */
    public function render($container = null): string
    {
        $dom = $this->getDomSitemap($container);
        $xml = $this->getUseXmlDeclaration() ?
            $dom->saveXML() :
            $dom->saveXML($dom->documentElement);

        return rtrim((string) $xml, PHP_EOL);
    }

    /**
     * Returns a DOMDocument containing the Sitemap XML for the given container
     *
     * @param ContainerInterface|string|null $container [optional] container to get
     *                                                  sitemaps from, defaults
     *                                                  to what is registered in the
     *                                                  helper
     * @param int|null                       $minDepth  [optional] minimum depth
     *                                                  required for page to be
     *                                                  valid. Default is to use
     *                                                  {@link getMinDepth()}. A
     *                                                  null value means no minimum
     *                                                  depth required.
     * @param int|null                       $maxDepth  [optional] maximum depth
     *                                                  a page can have to be
     *                                                  valid. Default is to use
     *                                                  {@link getMaxDepth()}. A
     *                                                  null value means no maximum
     *                                                  depth required.
     *
     * @throws Exception\RuntimeException         if schema validation is on
     *                                            and the sitemap is invalid
     *                                            according to the sitemap
     *                                            schema, or if sitemap
     *                                            validators are used and the
     *                                            loc element fails validation
     * @throws Exception\InvalidArgumentException
     *
     * @return \DOMDocument DOM representation of the container
     */
    public function getDomSitemap($container = null, ?int $minDepth = null, ?int $maxDepth = -1): \DOMDocument
    {
        // Reset the urls
        $this->urls = [];

        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        // create document
        $dom               = $this->getDom();
        $dom->formatOutput = $this->getFormatOutput();

        // ...and urlset (root) element
        $urlSet = $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'urlset');
        $dom->appendChild($urlSet);

        // create iterator
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

        if (!is_int($minDepth)) {
            $minDepth = $this->getMinDepth();
        }

        if ((!is_int($maxDepth) || 0 > $maxDepth) && null !== $maxDepth) {
            $maxDepth = $this->getMaxDepth();
        }

        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        foreach ($iterator as $page) {
            \assert(
                $page instanceof PageInterface,
                sprintf(
                    '$page should be an Instance of %s, but was %s',
                    PageInterface::class,
                    get_class($page)
                )
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
                            $url
                        ),
                        0,
                        $e
                    );
                }

                if (!$isValid) {
                    throw new Exception\RuntimeException(
                        sprintf(
                            'Encountered an invalid URL for Sitemap XML: "%s"',
                            $url
                        )
                    );
                }
            }

            // put url in 'loc' element
            $urlNode->appendChild($dom->createElementNS(SitemapInterface::SITEMAP_NS, 'loc', $url));

            // add 'lastmod' element if a valid lastmod is set in page
            if (isset($page->lastmod)) {
                $lastmod = strtotime((string) $page->lastmod);

                // prevent 1970-01-01...
                if (false !== $lastmod) {
                    $lastmod = date('c', $lastmod);
                }

                $lastmodValidator = $this->getLastmodValidator();

                try {
                    $isValid = $lastmodValidator->isValid($lastmod);
                } catch (RuntimeException $e) {
                    $this->logger->err($e);

                    $isValid = false;
                }

                if (
                    !$this->getUseSitemapValidators()
                    || (false !== $lastmod && $isValid)
                ) {
                    // Cast $lastmod to string in case no validation was used
                    $urlNode->appendChild(
                        $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'lastmod', (string) $lastmod)
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
                    $this->logger->err($e);

                    $isValid = false;
                }

                if (
                    !$this->getUseSitemapValidators()
                    || $isValid
                ) {
                    $urlNode->appendChild(
                        $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'changefreq', $changefreq)
                    );
                }
            }

            // add 'priority' element if a valid priority is set in page
            if (!isset($page->priority)) {
                continue;
            }

            $priority = $page->priority;

            if ($this->getUseSitemapValidators()) {
                $priorityValidator = $this->getPriorityValidator();

                try {
                    $isValid = $priorityValidator->isValid($priority);
                } catch (RuntimeException $e) {
                    $this->logger->err($e);

                    continue;
                }

                if (!$isValid) {
                    continue;
                }
            }

            $urlNode->appendChild(
                $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'priority', $priority)
            );
        }

        // validate using schema if specified
        if ($this->getUseSchemaValidation()) {
            ErrorHandler::start();

            $dom->schemaValidate(SitemapInterface::SITEMAP_XSD);

            try {
                ErrorHandler::stop(true);
            } catch (\ErrorException $e) {
                throw new Exception\RuntimeException(
                    sprintf(
                        'Sitemap is invalid according to XML Schema at "%s"',
                        SitemapInterface::SITEMAP_XSD
                    ),
                    0,
                    $e
                );
            }
        }

        return $dom;
    }

    /**
     * Returns an escaped absolute URL for the given page
     *
     * @param PageInterface $page
     *
     * @return string
     */
    public function url(PageInterface $page): string
    {
        $href = $page->getHref();

        if ('' === $href) {
            // no href
            return '';
        }

        if ('/' === mb_substr($href, 0, 1)) {
            // href is relative to root; use serverUrl helper
            $url = $this->getServerUrl() . $href;
        } elseif (preg_match('/^[a-z]+:/im', $href)) {
            // scheme is given in href; assume absolute URL already
            $url = $href;
        } else {
            // href is relative to current document; use url helpers
            $curDoc = ($this->basePathHelper)();
            $curDoc = '/' === $curDoc ? '' : trim($curDoc, '/');
            $url    = rtrim($this->getServerUrl(), '/') . '/' . $curDoc . ('' === $curDoc ? '' : '/') . $href;
        }

        if (!in_array($url, $this->urls, true)) {
            $this->urls[] = $url;

            return $this->xmlEscape($url);
        }

        return '';
    }

    /**
     * Escapes string for XML usage
     *
     * @param string $string
     *
     * @return string
     */
    private function xmlEscape(string $string): string
    {
        return ($this->escaper)($string);
    }

    /**
     * Sets whether XML output should be formatted
     *
     * @param bool $formatOutput
     *
     * @return self
     */
    public function setFormatOutput(bool $formatOutput = true): self
    {
        $this->formatOutput = $formatOutput;

        return $this;
    }

    /**
     * Returns whether XML output should be formatted
     *
     * @return bool
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
     * @return self
     */
    public function setServerUrl($uri): self
    {
        if (is_string($uri)) {
            try {
                $uri = Uri\UriFactory::factory($uri);
            } catch (InvalidArgumentException $e) {
                throw new Exception\InvalidArgumentException(
                    'Invalid server URL',
                    0,
                    $e
                );
            }
        }

        if (!$uri instanceof UriInterface) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '$serverUrl should be aa string or an Instance of %s',
                    UriInterface::class
                )
            );
        }

        try {
            $uri->setFragment('');
        } catch (InvalidUriPartException $e) {
            throw new Exception\InvalidArgumentException(
                'Invalid server URL',
                0,
                $e
            );
        }

        $uri->setPath('');
        $uri->setQuery('');

        if (!$uri->isValid()) {
            throw new Exception\InvalidArgumentException(
                'Invalid server URL'
            );
        }

        try {
            $this->serverUrl = $uri->toString();
        } catch (InvalidUriException $e) {
            throw new Exception\InvalidArgumentException(
                'Invalid server URL',
                0,
                $e
            );
        }

        return $this;
    }

    /**
     * Returns server URL
     *
     * @return string
     */
    public function getServerUrl(): string
    {
        if (null === $this->serverUrl) {
            $this->serverUrl = ($this->serverUrlHelper)();
        }

        return $this->serverUrl;
    }

    /**
     * Sets whether sitemap should be validated using Laminas\Validate\Sitemap_*
     *
     * @param bool $useSitemapValidators
     *
     * @return self
     */
    public function setUseSitemapValidators(bool $useSitemapValidators): self
    {
        $this->useSitemapValidators = $useSitemapValidators;

        return $this;
    }

    /**
     * Returns whether sitemap should be validated using Laminas\Validate\Sitemap_*
     *
     * @return bool
     */
    public function getUseSitemapValidators(): bool
    {
        return $this->useSitemapValidators;
    }

    /**
     * Sets whether sitemap should be schema validated when generated
     *
     * @param bool $schemaValidation
     *
     * @return self
     */
    public function setUseSchemaValidation(bool $schemaValidation): self
    {
        $this->useSchemaValidation = $schemaValidation;

        return $this;
    }

    /**
     * Returns true if sitemap should be schema validated when generated
     *
     * @return bool
     */
    public function getUseSchemaValidation(): bool
    {
        return $this->useSchemaValidation;
    }

    /**
     * Sets whether the XML declaration should be used in output
     *
     * @param bool $useXmlDecl
     *
     * @return self
     */
    public function setUseXmlDeclaration(bool $useXmlDecl): self
    {
        $this->useXmlDeclaration = $useXmlDecl;

        return $this;
    }

    /**
     * Returns whether the XML declaration should be used in output
     *
     * @return bool
     */
    public function getUseXmlDeclaration(): bool
    {
        return $this->useXmlDeclaration;
    }

    /**
     * @return \DOMDocument
     */
    public function getDom(): \DOMDocument
    {
        if (null === $this->dom) {
            $this->dom = new \DOMDocument('1.0', 'UTF-8');
        }

        return $this->dom;
    }

    /**
     * @param \DOMDocument $dom
     *
     * @return self
     */
    public function setDom(\DOMDocument $dom): self
    {
        $this->dom = $dom;

        return $this;
    }

    /**
     * @return Loc
     */
    public function getLocValidator(): Loc
    {
        if (null === $this->locValidator) {
            $this->locValidator = new Loc();
        }

        return $this->locValidator;
    }

    /**
     * @param Loc $locValidator
     *
     * @return self
     */
    public function setLocValidator(Loc $locValidator): self
    {
        $this->locValidator = $locValidator;

        return $this;
    }

    /**
     * @return Lastmod
     */
    public function getLastmodValidator(): Lastmod
    {
        if (null === $this->lastmodValidator) {
            $this->lastmodValidator = new Lastmod();
        }

        return $this->lastmodValidator;
    }

    /**
     * @param Lastmod $lastmodValidator
     *
     * @return self
     */
    public function setLastmodValidator(Lastmod $lastmodValidator): self
    {
        $this->lastmodValidator = $lastmodValidator;

        return $this;
    }

    /**
     * @return Priority
     */
    public function getPriorityValidator(): Priority
    {
        if (null === $this->priorityValidator) {
            $this->priorityValidator = new Priority();
        }

        return $this->priorityValidator;
    }

    /**
     * @param Priority $priorityValidator
     *
     * @return self
     */
    public function setPriorityValidator(Priority $priorityValidator): self
    {
        $this->priorityValidator = $priorityValidator;

        return $this;
    }

    /**
     * @return Changefreq
     */
    public function getChangefreqValidator(): Changefreq
    {
        if (null === $this->changefreqValidator) {
            $this->changefreqValidator = new Changefreq();
        }

        return $this->changefreqValidator;
    }

    /**
     * @param Changefreq $changefreqValidator
     *
     * @return self
     */
    public function setChangefreqValidator(Changefreq $changefreqValidator): self
    {
        $this->changefreqValidator = $changefreqValidator;

        return $this;
    }
}
