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

use DOMDocument;
use Laminas\Uri\UriInterface;
use Laminas\View\Exception;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Page\PageInterface;

interface SitemapInterface extends ViewHelperInterface
{
    /**
     * Namespace for the <urlset> tag
     */
    public const SITEMAP_NS = 'https://www.sitemaps.org/schemas/sitemap/0.9';

    /**
     * Schema URL
     */
    public const SITEMAP_XSD = 'https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd';

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
     * @return DOMDocument DOM representation of the container
     *
     * @throws Exception\RuntimeException         if schema validation is on
     *                                            and the sitemap is invalid
     *                                            according to the sitemap
     *                                            schema, or if sitemap
     *                                            validators are used and the
     *                                            loc element fails validation
     * @throws Exception\InvalidArgumentException
     */
    public function getDomSitemap($container = null, ?int $minDepth = null, ?int $maxDepth = -1): DOMDocument;

    /**
     * Returns an escaped absolute URL for the given page
     */
    public function url(PageInterface $page): string;

    /**
     * Sets whether XML output should be formatted
     *
     * @return self
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setFormatOutput(bool $formatOutput = true);

    /**
     * Returns whether XML output should be formatted
     */
    public function getFormatOutput(): bool;

    /**
     * Sets server url (scheme and host-related stuff without request URI)
     *
     * E.g. http://www.example.com
     *
     * @param string|UriInterface $serverUrl
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return self
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setServerUrl($serverUrl);

    /**
     * Returns server URL
     */
    public function getServerUrl(): string;

    /**
     * Sets whether sitemap should be validated using Laminas\Validate\Sitemap_*
     *
     * @return self
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setUseSitemapValidators(bool $useSitemapValidators);

    /**
     * Returns whether sitemap should be validated using Laminas\Validate\Sitemap_*
     */
    public function getUseSitemapValidators(): bool;

    /**
     * Sets whether sitemap should be schema validated when generated
     *
     * @return self
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setUseSchemaValidation(bool $schemaValidation);

    /**
     * Returns true if sitemap should be schema validated when generated
     */
    public function getUseSchemaValidation(): bool;

    /**
     * Sets whether the XML declaration should be used in output
     *
     * @return self
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setUseXmlDeclaration(bool $useXmlDecl);

    /**
     * Returns whether the XML declaration should be used in output
     */
    public function getUseXmlDeclaration(): bool;
}
