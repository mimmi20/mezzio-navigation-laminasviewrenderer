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

use Laminas\View\Exception;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Page\PageInterface;

interface SitemapInterface extends HelperInterface
{
    /**
     * Namespace for the <urlset> tag
     */
    public const SITEMAP_NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    /**
     * Schema URL
     */
    public const SITEMAP_XSD = 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd';

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
    public function getDomSitemap(?ContainerInterface $container = null): \DOMDocument;

    /**
     * Returns an escaped absolute URL for the given page
     *
     * @param PageInterface $page
     *
     * @return string
     */
    public function url(PageInterface $page): string;

    /**
     * Sets whether XML output should be formatted
     *
     * @param bool $formatOutput
     *
     * @return self
     */
    public function setFormatOutput(bool $formatOutput = true);

    /**
     * Returns whether XML output should be formatted
     *
     * @return bool
     */
    public function getFormatOutput(): bool;

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
    public function setServerUrl(string $serverUrl);

    /**
     * Returns server URL
     *
     * @return string
     */
    public function getServerUrl(): string;

    /**
     * Sets whether sitemap should be validated using Laminas\Validate\Sitemap_*
     *
     * @param bool $useSitemapValidators
     *
     * @return self
     */
    public function setUseSitemapValidators(bool $useSitemapValidators);

    /**
     * Returns whether sitemap should be validated using Laminas\Validate\Sitemap_*
     *
     * @return bool
     */
    public function getUseSitemapValidators(): bool;

    /**
     * Sets whether sitemap should be schema validated when generated
     *
     * @param bool $schemaValidation
     *
     * @return self
     */
    public function setUseSchemaValidation(bool $schemaValidation);

    /**
     * Returns true if sitemap should be schema validated when generated
     *
     * @return bool
     */
    public function getUseSchemaValidation(): bool;

    /**
     * Sets whether the XML declaration should be used in output
     *
     * @param bool $useXmlDecl
     *
     * @return self
     */
    public function setUseXmlDeclaration(bool $useXmlDecl);

    /**
     * Returns whether the XML declaration should be used in output
     *
     * @return bool
     */
    public function getUseXmlDeclaration(): bool;
}