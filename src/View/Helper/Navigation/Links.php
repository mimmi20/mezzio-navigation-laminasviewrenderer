<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use ErrorException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\ErrorHandler;
use Laminas\Stdlib\Exception\DomainException;
use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\Helper\HeadLink;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Exception\ExceptionInterface;
use Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\FindFromProperty\FindFromPropertyInterface;
use Mimmi20\NavigationHelper\FindRoot\FindRootInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;
use RecursiveIteratorIterator;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_search;
use function array_values;
use function assert;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function mb_strlen;
use function mb_strtolower;
use function method_exists;
use function preg_match;
use function rtrim;
use function sprintf;
use function ucfirst;

use const E_WARNING;
use const PHP_EOL;

/**
 * Helper for printing <link> elements
 */
final class Links extends AbstractHtmlElement implements LinksInterface
{
    use HelperTrait {
        __call as parentCall;
    }

    /**
     * Maps render constants to W3C link types
     *
     * @var array<int, string>
     */
    private static array $RELATIONS = [
        LinksInterface::RENDER_ALTERNATE => 'alternate',
        LinksInterface::RENDER_STYLESHEET => 'stylesheet',
        LinksInterface::RENDER_START => 'start',
        LinksInterface::RENDER_NEXT => 'next',
        LinksInterface::RENDER_PREV => 'prev',
        LinksInterface::RENDER_CONTENTS => 'contents',
        LinksInterface::RENDER_INDEX => 'index',
        LinksInterface::RENDER_GLOSSARY => 'glossary',
        LinksInterface::RENDER_COPYRIGHT => 'copyright',
        LinksInterface::RENDER_CHAPTER => 'chapter',
        LinksInterface::RENDER_SECTION => 'section',
        LinksInterface::RENDER_SUBSECTION => 'subsection',
        LinksInterface::RENDER_APPENDIX => 'appendix',
        LinksInterface::RENDER_HELP => 'help',
        LinksInterface::RENDER_BOOKMARK => 'bookmark',
    ];

    /**
     * The helper's render flag
     *
     * @see render()
     * @see setRenderFlag()
     */
    private int $renderFlag = LinksInterface::RENDER_ALL;

    /**
     * FindRoot helper
     */
    private FindRootInterface $rootFinder;

    /** @throws void */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        LoggerInterface $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        FindRootInterface $rootFinder,
        private HeadLink $headLink,
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;
        $this->rootFinder      = $rootFinder;
    }

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
     * @throws Exception\ExceptionInterface
     * @throws ExceptionInterface
     * @throws ErrorException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function __call(string $method, array $arguments = []): mixed
    {
        ErrorHandler::start(E_WARNING);
        $result = preg_match('/find(Rel|Rev)(.+)/', $method, $match);
        ErrorHandler::stop();

        if ($result && $arguments[0] instanceof PageInterface) {
            return $this->findRelation(
                $arguments[0],
                mb_strtolower($match[1]),
                mb_strtolower($match[2]),
            );
        }

        return $this->parentCall($method, $arguments);
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
     * @throws Exception\DomainException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function render(ContainerInterface | string | null $container = null): string
    {
        $container = $this->containerParser->parseContainer($container);

        if ($container === null) {
            $container = $this->getContainer();
        }

        $active = $this->findActive($container);

        if (!array_key_exists('page', $active) || !$active['page'] instanceof PageInterface) {
            // no active page
            return '';
        }

        $active = $active['page'];

        $output = '';
        $indent = $this->getIndent();

        $this->rootFinder->setRoot($container);

        try {
            $result = $this->findAllRelations($active, $this->getRenderFlag());
        } catch (\Laminas\Stdlib\Exception\InvalidArgumentException $e) {
            $this->logger->error($e);

            return '';
        }

        foreach ($result as $attrib => $types) {
            if (!is_string($attrib)) {
                continue;
            }

            foreach ($types as $relation => $pages) {
                if (!is_string($relation)) {
                    continue;
                }

                foreach ($pages as $page) {
                    $r = $this->renderLink($page, $attrib, $relation);

                    if ($r === '') {
                        continue;
                    }

                    $output .= $indent . $r . PHP_EOL;
                }
            }
        }

        $this->rootFinder->setRoot(null);

        // return output (trim last newline by spec)
        return mb_strlen($output) ? rtrim($output, PHP_EOL) : '';
    }

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
    public function renderLink(PageInterface $page, string $attrib, string $relation): string
    {
        if (!in_array($attrib, ['rel', 'rev'], true)) {
            throw new Exception\DomainException(
                sprintf(
                    'Invalid relation attribute "%s", must be "rel" or "rev"',
                    $attrib,
                ),
            );
        }

        $href = $page->getHref();

        if (!$href) {
            return '';
        }

        // TODO: add more attribs
        // http://www.w3.org/TR/html401/struct/links.html#h-12.2
        $attribs = [
            $attrib => $relation,
            'href' => $href,
            'title' => $page->getLabel(),
        ];

        $otherAttributes = ['type', 'hreflang', 'charset', 'lang', 'media'];

        foreach ($otherAttributes as $otherAttributeName) {
            try {
                $otherAttributeValue = $page->get($otherAttributeName);
            } catch (InvalidArgumentException) {
                continue;
            }

            if ($otherAttributeValue === null) {
                continue;
            }

            $attribs[$otherAttributeName] = $otherAttributeValue;
        }

        return $this->headLink->itemToString((object) $attribs);
    }

    // Finder methods:

    /**
     * Finds all relations (forward and reverse) for the given $page
     *
     * The form of the returned array:
     * <code>
     * // $page denotes an instance of Mimmi20\Mezzio\Navigation\Page\PageInterface
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
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function findAllRelations(PageInterface $page, int | null $flag = null): array
    {
        if (!is_int($flag)) {
            $flag = self::RENDER_ALL;
        }

        $result = ['rel' => [], 'rev' => []];
        $native = array_values(self::$RELATIONS);

        foreach (array_keys($result) as $rel) {
            $meth  = 'getDefined' . ucfirst($rel);
            $types = array_merge($native, array_diff($page->{$meth}(), $native));

            foreach ($types as $type) {
                $relFlag = array_search($type, self::$RELATIONS, true);

                if (!$relFlag) {
                    $relFlag = self::RENDER_CUSTOM;
                }

                if (!((int) $flag & (int) $relFlag)) {
                    continue;
                }

                try {
                    $found = $this->findRelation($page, $rel, $type);
                } catch (Exception\DomainException) {
                    continue;
                }

                if (!$found) {
                    continue;
                }

                $result[$rel][$type] = $found;
            }
        }

        return $result;
    }

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
     * @throws Exception\DomainException                          if $rel is not "rel" or "rev"
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function findRelation(PageInterface $page, string $rel, string $type): array
    {
        if (!in_array($rel, ['rel', 'rev'], true)) {
            throw new Exception\DomainException(
                sprintf(
                    'Invalid argument: $rel must be "rel" or "rev"; "%s" given',
                    $rel,
                ),
            );
        }

        $result = $this->findFromProperty($page, $rel, $type);

        if (!$result) {
            $result = $this->findFromSearch($page, $rel, $type);

            if ($result === null) {
                return [];
            }

            if (!is_array($result)) {
                $result = [$result];
            }
        }

        return $result;
    }

    // Search methods:

    /**
     * Searches the root container for the forward 'start' relation of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to the first document in a collection of documents. This link type
     * tells search engines which document is considered by the author to be the
     * starting point of the collection.
     *
     * @throws void
     */
    public function searchRelStart(PageInterface $page): PageInterface | null
    {
        $found = $this->rootFinder->find($page);

        if (!$found instanceof PageInterface) {
            $found->rewind();
            $found = $found->current();
        }

        if ($found === $page || !$this->accept($found)) {
            $found = null;
        }

        return $found;
    }

    /**
     * Searches the root container for the forward 'next' relation of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to the next document in a linear sequence of documents. User
     * agents may choose to preload the "next" document, to reduce the perceived
     * load time.
     *
     * @throws void
     */
    public function searchRelNext(PageInterface $page): PageInterface | null
    {
        $found = null;
        $break = false;

        /** @var RecursiveIteratorIterator<PageInterface> $iterator */
        $iterator = new RecursiveIteratorIterator(
            $this->rootFinder->find($page),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $intermediate) {
            assert($intermediate instanceof PageInterface);

            if ($intermediate === $page) {
                // current page; break at next accepted page
                $break = true;

                continue;
            }

            if ($break && $this->accept($intermediate)) {
                $found = $intermediate;

                break;
            }
        }

        return $found;
    }

    /**
     * Searches the root container for the forward 'prev' relation of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to the previous document in an ordered series of documents. Some
     * user agents also support the synonym "Previous".
     *
     * @throws void
     */
    public function searchRelPrev(PageInterface $page): PageInterface | null
    {
        $found = null;
        $prev  = null;

        /** @var RecursiveIteratorIterator<PageInterface> $iterator */
        $iterator = new RecursiveIteratorIterator(
            $this->rootFinder->find($page),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $intermediate) {
            assert($intermediate instanceof PageInterface);

            if (!$this->accept($intermediate)) {
                continue;
            }

            if ($intermediate === $page) {
                $found = $prev;

                break;
            }

            $prev = $intermediate;
        }

        return $found;
    }

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
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function searchRelChapter(PageInterface $page): array
    {
        $found = [];

        // find first level of pages
        $root = $this->rootFinder->find($page);

        // find start page(s)
        $start = $this->findRelation($page, 'rel', 'start');

        foreach ($root as $chapter) {
            // exclude self and start page from chapters
            if ($chapter === $page || in_array($chapter, $start, true) || !$this->accept($chapter)) {
                continue;
            }

            $found[] = $chapter;
        }

        return $found;
    }

    /**
     * Searches the root container for forward 'section' relations of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a section in a collection of documents.
     *
     * @return array<PageInterface>
     *
     * @throws void
     */
    public function searchRelSection(PageInterface $page): array
    {
        if (!$page->hasPages()) {
            return [];
        }

        $root = $this->rootFinder->find($page);

        // check if given page has pages and is a chapter page
        if (!$root->hasPage($page)) {
            return [];
        }

        $found = [];

        foreach ($page as $section) {
            if (!$this->accept($section)) {
                continue;
            }

            $found[] = $section;
        }

        return $found;
    }

    /**
     * Searches the root container for forward 'subsection' relations of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a subsection in a collection of
     * documents.
     *
     * @return array<PageInterface>
     *
     * @throws void
     */
    public function searchRelSubsection(PageInterface $page): array
    {
        if (!$page->hasPages()) {
            return [];
        }

        $root  = $this->rootFinder->find($page);
        $found = [];

        // given page has child pages, loop chapters
        foreach ($root as $chapter) {
            // is page a section?
            if (!$chapter->hasPage($page)) {
                continue;
            }

            foreach ($page as $subsection) {
                if (!$this->accept($subsection)) {
                    continue;
                }

                $found[] = $subsection;
            }
        }

        return $found;
    }

    /**
     * Searches the root container for the reverse 'section' relation of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a section in a collection of documents.
     *
     * @throws void
     */
    public function searchRevSection(PageInterface $page): PageInterface | null
    {
        $parent = $page->getParent();

        if (!$parent instanceof PageInterface) {
            return null;
        }

        $root  = $this->rootFinder->find($page);
        $found = null;

        if ($root->hasPage($parent)) {
            $found = $parent;
        }

        return $found;
    }

    /**
     * Searches the root container for the reverse 'section' relation of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a subsection in a collection of
     * documents.
     *
     * @throws void
     */
    public function searchRevSubsection(PageInterface $page): PageInterface | null
    {
        $parent = $page->getParent();

        if (!$parent instanceof PageInterface) {
            return null;
        }

        $root  = $this->rootFinder->find($page);
        $found = null;

        foreach ($root as $chapter) {
            if ($chapter->hasPage($parent)) {
                $found = $parent;

                break;
            }
        }

        return $found;
    }

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
     * @throws void
     */
    public function setRenderFlag(int $renderFlag): self
    {
        $this->renderFlag = $renderFlag;

        return $this;
    }

    /**
     * Returns the helper's render flag
     *
     * @throws void
     */
    public function getRenderFlag(): int
    {
        return $this->renderFlag;
    }

    /**
     * Finds relations of given $type for $page by checking if the
     * relation is specified as a property of $page
     *
     * @param PageInterface $page page to find relations for
     * @param string        $rel  relation, 'rel' or 'rev'
     * @param string        $type link type, e.g. 'start', 'next'
     *
     * @return array<PageInterface>
     *
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    private function findFromProperty(PageInterface $page, string $rel, string $type): array
    {
        try {
            $findFromPropertyHelper = $this->serviceLocator->build(
                FindFromPropertyInterface::class,
                [
                    'authorization' => $this->getUseAuthorization() ? $this->getAuthorization() : null,
                    'renderInvisible' => $this->getRenderInvisible(),
                    'role' => $this->getRole(),
                ],
            );
        } catch (ContainerExceptionInterface $e) {
            $this->logger->error($e);

            return [];
        }

        assert($findFromPropertyHelper instanceof FindFromPropertyInterface);

        return $findFromPropertyHelper->find($page, $rel, $type);
    }

    /**
     * Finds relations of given $rel=$type for $page by using the helper to
     * search for the relation in the root container
     *
     * @param PageInterface $page page to find relations for
     * @param string        $rel  relation, 'rel' or 'rev'
     * @param string        $type link type, e.g. 'start', 'next', etc
     *
     * @return array<PageInterface>|PageInterface|null
     *
     * @throws void
     */
    private function findFromSearch(PageInterface $page, string $rel, string $type): array | PageInterface | null
    {
        $found = null;

        $method = 'search' . ucfirst($rel) . ucfirst($type);

        if (method_exists($this, $method)) {
            $found = $this->{$method}($page);
        }

        return $found;
    }
}
