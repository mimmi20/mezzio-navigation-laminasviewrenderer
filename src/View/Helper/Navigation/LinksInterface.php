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

use ErrorException;
use Laminas\View\Exception;
use Mezzio\Navigation\Exception\ExceptionInterface;
use Mezzio\Navigation\Exception\InvalidArgumentException;
use Mezzio\Navigation\Page\PageInterface;

interface LinksInterface extends ViewHelperInterface
{
    /**
     * Constants used for specifying which link types to find and render
     */
    public const RENDER_ALTERNATE  = 0x0001;
    public const RENDER_STYLESHEET = 0x0002;
    public const RENDER_START      = 0x0004;
    public const RENDER_NEXT       = 0x0008;
    public const RENDER_PREV       = 0x0010;
    public const RENDER_CONTENTS   = 0x0020;
    public const RENDER_INDEX      = 0x0040;
    public const RENDER_GLOSSARY   = 0x0080;
    public const RENDER_COPYRIGHT  = 0x0100;
    public const RENDER_CHAPTER    = 0x0200;
    public const RENDER_SECTION    = 0x0400;
    public const RENDER_SUBSECTION = 0x0800;
    public const RENDER_APPENDIX   = 0x1000;
    public const RENDER_HELP       = 0x2000;
    public const RENDER_BOOKMARK   = 0x4000;
    public const RENDER_CUSTOM     = 0x8000;
    public const RENDER_ALL        = 0xffff;

    /**
     * Magic overload: Proxy calls to {@link findRelation()} or container
     *
     * Examples of finder calls:
     * <code>
     * // METHOD                  // SAME AS
     * $h->findRelNext($page);    // $h->findRelation($page, 'rel', 'next')
     * $h->findRevSection($page); // $h->findRelation($page, 'rev', 'section');
     * $h->findRelFoo($page);     // $h->findRelation($page, 'rel', 'foo');
     * </code>
     *
     * @param array<mixed> $arguments
     *
     * @return mixed
     *
     * @throws Exception\ExceptionInterface
     * @throws ExceptionInterface
     * @throws ErrorException
     */
    public function __call(string $method, array $arguments = []);

    /**
     * Renders the given $page as a link element, with $attrib = $relation
     *
     * @param PageInterface $page     the page to render the link for
     * @param string        $attrib   the attribute to use for $type,
     *                                either 'rel' or 'rev'
     * @param string        $relation relation type, muse be one of;
     *                                alternate, appendix, bookmark,
     *                                chapter, contents, copyright,
     *                                glossary, help, home, index, next,
     *                                prev, section, start, stylesheet,
     *                                subsection
     *
     * @throws Exception\DomainException
     */
    public function renderLink(PageInterface $page, string $attrib, string $relation): string;

    // Finder methods:

    /**
     * Finds all relations (forward and reverse) for the given $page
     *
     * The form of the returned array:
     * <code>
     * // $page denotes an instance of Mezzio\Navigation\Page\PageInterface
     * $returned = array(
     *     'rel' => array(
     *         'alternate' => array($page, $page, $page),
     *         'start'     => array($page),
     *         'next'      => array($page),
     *         'prev'      => array($page),
     *         'canonical' => array($page)
     *     ),
     *     'rev' => array(
     *         'section'   => array($page)
     *     )
     * );
     * </code>
     *
     * @param PageInterface $page page to find links for
     *
     * @return array<string, array<int|string, array<int|string, PageInterface>>>
     *
     * @throws InvalidArgumentException
     */
    public function findAllRelations(PageInterface $page, ?int $flag = null): array;

    /**
     * Finds relations of the given $rel=$type from $page
     *
     * This method will first look for relations in the page instance, then
     * by searching the root container if nothing was found in the page.
     *
     * @param PageInterface $page page to find relations for
     * @param string        $rel  relation, "rel" or "rev"
     * @param string        $type link type, e.g. 'start', 'next'
     *
     * @return array<PageInterface>
     *
     * @throws Exception\DomainException if $rel is not "rel" or "rev"
     * @throws InvalidArgumentException
     */
    public function findRelation(PageInterface $page, string $rel, string $type): array;

    // Search methods:

    /**
     * Searches the root container for the forward 'start' relation of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to the first document in a collection of documents. This link type
     * tells search engines which document is considered by the author to be the
     * starting point of the collection.
     */
    public function searchRelStart(PageInterface $page): ?PageInterface;

    /**
     * Searches the root container for the forward 'next' relation of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to the next document in a linear sequence of documents. User
     * agents may choose to preload the "next" document, to reduce the perceived
     * load time.
     */
    public function searchRelNext(PageInterface $page): ?PageInterface;

    /**
     * Searches the root container for the forward 'prev' relation of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to the previous document in an ordered series of documents. Some
     * user agents also support the synonym "Previous".
     */
    public function searchRelPrev(PageInterface $page): ?PageInterface;

    /**
     * Searches the root container for forward 'chapter' relations of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a chapter in a collection of documents.
     *
     * @return array<PageInterface>
     *
     * @throws Exception\DomainException
     * @throws InvalidArgumentException
     */
    public function searchRelChapter(PageInterface $page): array;

    /**
     * Searches the root container for forward 'section' relations of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a section in a collection of documents.
     *
     * @return array<PageInterface>
     */
    public function searchRelSection(PageInterface $page): array;

    /**
     * Searches the root container for forward 'subsection' relations of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a subsection in a collection of
     * documents.
     *
     * @return array<PageInterface>
     */
    public function searchRelSubsection(PageInterface $page): array;

    /**
     * Searches the root container for the reverse 'section' relation of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a section in a collection of documents.
     */
    public function searchRevSection(PageInterface $page): ?PageInterface;

    /**
     * Searches the root container for the reverse 'section' relation of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a subsection in a collection of
     * documents.
     */
    public function searchRevSubsection(PageInterface $page): ?PageInterface;

    // Util methods:

    /**
     * Sets the helper's render flag
     *
     * The helper uses the bitwise '&' operator against the hex values of the
     * render constants. This means that the flag can is "bitwised" value of
     * the render constants. Examples:
     * <code>
     * // render all links except glossary
     * $flag = Links:RENDER_ALL ^ Links:RENDER_GLOSSARY;
     * $helper->setRenderFlag($flag);
     *
     * // render only chapters and sections
     * $flag = Links:RENDER_CHAPTER | Links:RENDER_SECTION;
     * $helper->setRenderFlag($flag);
     *
     * // render only relations that are not native W3C relations
     * $helper->setRenderFlag(Links:RENDER_CUSTOM);
     *
     * // render all relations (default)
     * $helper->setRenderFlag(Links:RENDER_ALL);
     * </code>
     *
     * Note that custom relations can also be rendered directly using the
     * {@link renderLink()} method.
     *
     * @return self
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setRenderFlag(int $renderFlag);

    /**
     * Returns the helper's render flag
     */
    public function getRenderFlag(): int;
}
