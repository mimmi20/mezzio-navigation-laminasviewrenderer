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

use Laminas\Log\Logger;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\ErrorHandler;
use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHtmlElement;
use Laminas\View\Helper\HeadLink;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Exception\InvalidArgumentException;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\FindRootInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\Page\PageFactory;
use Mezzio\Navigation\Page\PageInterface;
use RecursiveIteratorIterator;
use Traversable;

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
     * @var array
     */
    private static $RELATIONS = [
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
     *
     * @var int
     */
    private $renderFlag = LinksInterface::RENDER_ALL;

    /**
     * FindRoot helper
     *
     * @var FindRootInterface
     */
    private $rootFinder;

    /** @var HeadLink */
    private $headLink;

    /**
     * @param \Interop\Container\ContainerInterface $serviceLocator
     * @param Logger                                $logger
     * @param HtmlifyInterface                      $htmlify
     * @param ContainerParserInterface              $containerParser
     * @param FindRootInterface                     $rootFinder
     * @param HeadLink                              $headLink
     */
    public function __construct(
        \Interop\Container\ContainerInterface $serviceLocator,
        Logger $logger,
        HtmlifyInterface $htmlify,
        ContainerParserInterface $containerParser,
        FindRootInterface $rootFinder,
        HeadLink $headLink
    ) {
        $this->serviceLocator  = $serviceLocator;
        $this->logger          = $logger;
        $this->htmlify         = $htmlify;
        $this->containerParser = $containerParser;
        $this->rootFinder      = $rootFinder;
        $this->headLink        = $headLink;
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
     * @param string $method
     * @param array  $arguments
     *
     * @throws Exception\ExceptionInterface
     * @throws \Mezzio\Navigation\Exception\ExceptionInterface
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \ErrorException
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        ErrorHandler::start(E_WARNING);
        $result = preg_match('/find(Rel|Rev)(.+)/', $method, $match);
        ErrorHandler::stop();

        if ($result && $arguments[0] instanceof PageInterface) {
            return $this->findRelation($arguments[0], mb_strtolower($match[1]), mb_strtolower($match[2]));
        }

        return $this->parentCall($method, $arguments);
    }

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
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function render($container = null): string
    {
        $container = $this->containerParser->parseContainer($container);

        if (null === $container) {
            $container = $this->getContainer();
        }

        $active = $this->findActive($container);
        if (!$active) {
            // no active page
            return '';
        }

        $active = $active['page'];

        $output = '';
        $indent = $this->getIndent();

        $this->rootFinder->setRoot($container);

        $result = $this->findAllRelations($active, $this->getRenderFlag());
        foreach ($result as $attrib => $types) {
            foreach ($types as $relation => $pages) {
                foreach ($pages as $page) {
                    $r = $this->renderLink($page, $attrib, $relation);
                    if (!$r) {
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
     *
     * @return string
     */
    public function renderLink(PageInterface $page, string $attrib, string $relation): string
    {
        if (!in_array($attrib, ['rel', 'rev'], true)) {
            throw new Exception\DomainException(
                sprintf(
                    'Invalid relation attribute "%s", must be "rel" or "rev"',
                    $attrib
                )
            );
        }

        if (!$href = $page->getHref()) {
            return '';
        }

        // TODO: add more attribs
        // http://www.w3.org/TR/html401/struct/links.html#h-12.2
        $attribs = [
            $attrib => $relation,
            'href' => $href,
            'title' => $page->getLabel(),
        ];

        return $this->headLink->itemToString((object) $attribs);
    }

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
     * @param int|null      $flag
     *
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return array
     */
    public function findAllRelations(PageInterface $page, ?int $flag = null): array
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
                if (!$relFlag = array_search($type, self::$RELATIONS, true)) {
                    $relFlag = self::RENDER_CUSTOM;
                }

                if (!((int) $flag & (int) $relFlag)) {
                    continue;
                }

                $found = $this->findRelation($page, $rel, $type);
                if (!$found) {
                    continue;
                }

                if (!is_array($found)) {
                    $found = [$found];
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
     * @throws Exception\DomainException                             if $rel is not "rel" or "rev"
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return array|PageInterface|null
     */
    public function findRelation(PageInterface $page, string $rel, string $type)
    {
        if (!in_array($rel, ['rel', 'rev'], true)) {
            throw new Exception\DomainException(
                sprintf(
                    'Invalid argument: $rel must be "rel" or "rev"; "%s" given',
                    $rel
                )
            );
        }

        if (!$result = $this->findFromProperty($page, $rel, $type)) {
            $result = $this->findFromSearch($page, $rel, $type);
        }

        return $result;
    }

    /**
     * Finds relations of given $type for $page by checking if the
     * relation is specified as a property of $page
     *
     * @param PageInterface $page page to find relations for
     * @param string        $rel  relation, 'rel' or 'rev'
     * @param string        $type link type, e.g. 'start', 'next'
     *
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return array|PageInterface|null
     */
    private function findFromProperty(PageInterface $page, string $rel, string $type)
    {
        $method = 'get' . ucfirst($rel);
        $result = $page->{$method}($type);

        if (!$result) {
            return null;
        }

        $result = $this->convertToPages($result);

        if (!$result) {
            return null;
        }

        if (!is_array($result)) {
            $result = [$result];
        }

        $filtered = array_filter(
            $result,
            function (PageInterface $page) {
                return $this->accept($page);
            }
        );

        return 1 === count($filtered) ? $filtered[0] : $filtered;
    }

    /**
     * Finds relations of given $rel=$type for $page by using the helper to
     * search for the relation in the root container
     *
     * @param PageInterface $page page to find relations for
     * @param string        $rel  relation, 'rel' or 'rev'
     * @param string        $type link type, e.g. 'start', 'next', etc
     *
     * @return array|PageInterface|null
     */
    private function findFromSearch(PageInterface $page, string $rel, string $type)
    {
        $found = null;

        $method = 'search' . ucfirst($rel) . ucfirst($type);

        if (method_exists($this, $method)) {
            $found = $this->{$method}($page);
        }

        return $found;
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
     * @param PageInterface $page
     *
     * @return PageInterface|null
     */
    public function searchRelStart(PageInterface $page): ?PageInterface
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
     * @param PageInterface $page
     *
     * @return PageInterface|null
     */
    public function searchRelNext(PageInterface $page): ?PageInterface
    {
        $found    = null;
        $break    = false;
        $iterator = new RecursiveIteratorIterator($this->rootFinder->find($page), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $intermediate) {
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
     * @param PageInterface $page
     *
     * @return PageInterface|null
     */
    public function searchRelPrev(PageInterface $page): ?PageInterface
    {
        $found    = null;
        $prev     = null;
        $iterator = new RecursiveIteratorIterator(
            $this->rootFinder->find($page),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $intermediate) {
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
     * @param PageInterface $page
     *
     * @throws \Laminas\View\Exception\DomainException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return array|PageInterface|null
     */
    public function searchRelChapter(PageInterface $page)
    {
        $found = [];

        // find first level of pages
        $root = $this->rootFinder->find($page);

        // find start page(s)
        $start = $this->findRelation($page, 'rel', 'start');

        if (!is_array($start)) {
            $start = [$start];
        }

        foreach ($root as $chapter) {
            // exclude self and start page from chapters
            if (
                $chapter === $page ||
                in_array($chapter, $start, true) ||
                !$this->accept($chapter)
            ) {
                continue;
            }

            $found[] = $chapter;
        }

        switch (count($found)) {
            case 0:
                return null;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Searches the root container for forward 'section' relations of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a section in a collection of documents.
     *
     * @param PageInterface $page
     *
     * @return array|PageInterface|null
     */
    public function searchRelSection(PageInterface $page)
    {
        if (!$page->hasPages()) {
            return null;
        }

        $root = $this->rootFinder->find($page);

        // check if given page has pages and is a chapter page
        if (!$root->hasPage($page)) {
            return null;
        }

        $found = [];

        foreach ($page as $section) {
            if (!$this->accept($section)) {
                continue;
            }

            $found[] = $section;
        }

        switch (count($found)) {
            case 0:
                return null;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Searches the root container for forward 'subsection' relations of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a subsection in a collection of
     * documents.
     *
     * @param PageInterface $page
     *
     * @return array|PageInterface|null
     */
    public function searchRelSubsection(PageInterface $page)
    {
        if (!$page->hasPages()) {
            return null;
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

        switch (count($found)) {
            case 0:
                return null;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Searches the root container for the reverse 'section' relation of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a section in a collection of documents.
     *
     * @param PageInterface $page
     *
     * @return PageInterface|null
     */
    public function searchRevSection(PageInterface $page): ?PageInterface
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
     * @param PageInterface $page
     *
     * @return PageInterface|null
     */
    public function searchRevSubsection(PageInterface $page): ?PageInterface
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
     * Converts a $mixed value to an array of pages
     *
     * @param ContainerInterface|PageInterface|string|Traversable $mixed     mixed value to get page(s) from
     * @param bool                                                $recursive whether $value should be looped if it is an array or a config
     *
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     *
     * @return array|PageInterface|null
     */
    private function convertToPages($mixed, bool $recursive = true)
    {
        if ($mixed instanceof PageInterface) {
            // value is a page instance; return directly
            return $mixed;
        }

        if ($mixed instanceof ContainerInterface) {
            // value is a container; return pages in it
            $pages = [];
            foreach ($mixed as $page) {
                $pages[] = $page;
            }

            return $pages;
        }

        if (is_string($mixed)) {
            // value is a string; make a URI page
            try {
                return (new PageFactory())->factory(
                    [
                        'type' => 'uri',
                        'uri' => $mixed,
                    ]
                );
            } catch (InvalidArgumentException $e) {
                $this->logger->err($e);
            }
        }

        if ($mixed instanceof Traversable) {
            $mixed = ArrayUtils::iteratorToArray($mixed);
        }

        if (is_array($mixed) && !empty($mixed)) {
            if ($recursive && is_numeric(key($mixed))) {
                // first key is numeric; assume several pages
                $pages = array_filter(
                    $mixed,
                    function ($value) {
                        return $this->convertToPages($value, false);
                    }
                );

                return array_values($pages);
            }

            // pass array to factory directly
            try {
                return (new PageFactory())->factory($mixed);
            } catch (InvalidArgumentException $e) {
                $this->logger->err($e);
            }
        }

        // nothing found
        return null;
    }

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
     * @param int $renderFlag
     *
     * @return self
     */
    public function setRenderFlag(int $renderFlag): self
    {
        $this->renderFlag = $renderFlag;

        return $this;
    }

    /**
     * Returns the helper's render flag
     *
     * @return int
     */
    public function getRenderFlag(): int
    {
        return $this->renderFlag;
    }
}
