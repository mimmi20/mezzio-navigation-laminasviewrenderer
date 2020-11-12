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
namespace Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Laminas\Stdlib\ErrorHandler;
use Laminas\Uri;
use Laminas\Uri\Exception\InvalidArgumentException;
use Laminas\Uri\Exception\InvalidUriException;
use Laminas\Uri\Exception\InvalidUriPartException;
use Laminas\Validator\Sitemap\Changefreq;
use Laminas\Validator\Sitemap\Lastmod;
use Laminas\Validator\Sitemap\Loc;
use Laminas\Validator\Sitemap\Priority;
use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHtmlElement;
use Mezzio\Navigation\ContainerInterface;
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
     * @var string
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

    /**
     * Renders helper
     *
     * Implements {@link HelperInterface::render()}.
     *
     * @param ContainerInterface|string|null $container [optional] container to render.
     *                                                  Default is null, which indicates
     *                                                  that the helper should render
     *                                                  the container returned by {@link getContainer()}.
     *
     * @throws \Laminas\Validator\Exception\RuntimeException
     * @throws \Laminas\View\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
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
     * @param ContainerInterface|null $container [optional] container to get
     *                                           breadcrumbs from, defaults
     *                                           to what is registered in the
     *                                           helper
     *
     * @throws Exception\RuntimeException                            if schema validation is on
     *                                                               and the sitemap is invalid
     *                                                               according to the sitemap
     *                                                               schema, or if sitemap
     *                                                               validators are used and the
     *                                                               loc element fails validation
     * @throws \Laminas\Validator\Exception\RuntimeException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return \DOMDocument DOM representation of the container
     */
    public function getDomSitemap(?ContainerInterface $container = null): \DOMDocument
    {
        // Reset the urls
        $this->urls = [];

        if (null === $container) {
            $container = $this->getContainer();
        }

        // create document
        $dom               = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $this->getFormatOutput();

        // ...and urlset (root) element
        $urlSet = $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'urlset');
        $dom->appendChild($urlSet);

        // create iterator
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

        $maxDepth = $this->getMaxDepth();
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        $minDepth = $this->getMinDepth();
        if (!is_int($minDepth) || 0 > $minDepth) {
            $minDepth = 0;
        }

        // iterate container
        foreach ($iterator as $page) {
            if ($iterator->getDepth() < $minDepth || !$this->accept($page)) {
                // page should not be included
                continue;
            }

            // get absolute url from page
            if (!$url = $this->url($page)) {
                // skip page if it has no url (rare case)
                // or already is in the sitemap
                continue;
            }

            // create url node for this page
            $urlNode = $dom->createElementNS(SitemapInterface::SITEMAP_NS, 'url');
            $urlSet->appendChild($urlNode);

            if ($this->getUseSitemapValidators()) {
                $locValidator = new Loc();

                if (!$locValidator->isValid($url)) {
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

                $lastmodValidator = new Lastmod();

                if (
                    !$this->getUseSitemapValidators()
                    || (false !== $lastmod && $lastmodValidator->isValid($lastmod))
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
                $changefreqValidator = new Changefreq();

                if (
                    !$this->getUseSitemapValidators() ||
                    $changefreqValidator->isValid($changefreq)
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
                $priorityValidator = new Priority();

                if (!$priorityValidator->isValid($priority)) {
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

        if (!isset($href[0])) {
            // no href
            return '';
        }

        if ('/' === $href[0]) {
            // href is relative to root; use serverUrl helper
            $url = $this->getServerUrl() . $href;
        } elseif (preg_match('/^[a-z]+:/im', (string) $href)) {
            // scheme is given in href; assume absolute URL already
            $url = (string) $href;
        } else {
            // href is relative to current document; use url helpers
            $basePathHelper = $this->getView()->plugin('basepath');
            $curDoc         = $basePathHelper();
            $curDoc         = '/' === $curDoc ? '' : trim($curDoc, '/');
            $url            = rtrim($this->getServerUrl(), '/') . '/' . $curDoc . (empty($curDoc) ? '' : '/') . $href;
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
        $escaper = $this->getView()->plugin('escapeHtml');

        return $escaper($string);
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
     * @param string $serverUrl
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return self
     */
    public function setServerUrl(string $serverUrl): self
    {
        try {
            $uri = Uri\UriFactory::factory($serverUrl);
        } catch (InvalidArgumentException $e) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Invalid server URL: "%s"',
                    $serverUrl
                ),
                0,
                $e
            );
        }

        try {
            $uri->setFragment('');
        } catch (InvalidUriPartException $e) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Invalid server URL: "%s"',
                    $serverUrl
                ),
                0,
                $e
            );
        }

        $uri->setPath('');
        $uri->setQuery('');

        if (!$uri->isValid()) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Invalid server URL: "%s"',
                    $serverUrl
                )
            );
        }

        try {
            $this->serverUrl = $uri->toString();
        } catch (InvalidUriException $e) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Invalid server URL: "%s"',
                    $serverUrl
                ),
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
        if (!isset($this->serverUrl)) {
            $serverUrlHelper = $this->getView()->plugin('serverUrl');
            $this->serverUrl = $serverUrlHelper();
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
        $this->useSitemapValidators = (bool) $useSitemapValidators;

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
        $this->useSchemaValidation = (bool) $schemaValidation;

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
        $this->useXmlDeclaration = (bool) $useXmlDecl;

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
}
