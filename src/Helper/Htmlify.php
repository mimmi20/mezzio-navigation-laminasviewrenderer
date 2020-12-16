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
namespace Mezzio\Navigation\LaminasView\Helper;

use Laminas\I18n\View\Helper\Translate;
use Laminas\Json\Json;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;
use Mezzio\Navigation\Page\PageInterface;

final class Htmlify implements HtmlifyInterface
{
    /** @var Translate|null */
    private $translator;

    /** @var EscapeHtml */
    private $escaper;

    /** @var EscapeHtmlAttr */
    private $escapeHtmlAttr;

    /**
     * @param \Laminas\View\Helper\EscapeHtml          $escaper
     * @param \Laminas\View\Helper\EscapeHtmlAttr      $escapeHtmlAttr
     * @param \Laminas\I18n\View\Helper\Translate|null $translator
     */
    public function __construct(EscapeHtml $escaper, EscapeHtmlAttr $escapeHtmlAttr, ?Translate $translator = null)
    {
        $this->escaper        = $escaper;
        $this->escapeHtmlAttr = $escapeHtmlAttr;
        $this->translator     = $translator;
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page
     *
     * @param string        $prefix
     * @param PageInterface $page               page to generate HTML for
     * @param bool          $escapeLabel        Whether or not to escape the label
     * @param bool          $addClassToListItem Whether or not to add the page class to the list item
     *
     * @return string HTML string (<a href="…">Label</a>)
     */
    public function toHtml(string $prefix, PageInterface $page, bool $escapeLabel = true, bool $addClassToListItem = false): string
    {
        $label = (string) $page->getLabel();
        $title = (string) $page->getTitle();

        if (null !== $this->translator) {
            $label = ($this->translator)($label, $page->getTextDomain());
            $title = ($this->translator)($title, $page->getTextDomain());
        }

        // get attribs for element
        $attribs = [
            'id' => $page->getId(),
            'title' => $title,
        ];

        if (false === $addClassToListItem) {
            $attribs['class'] = $page->getClass();
        }

        // does page have a href?
        $href = $page->getHref();

        if ($href) {
            $element           = 'a';
            $attribs['href']   = $href;
            $attribs['target'] = $page->getTarget();
        } else {
            $element = 'span';
        }

        // remove sitemap specific attributes
        $attribs = array_diff_key(
            array_merge($attribs, $page->getCustomProperties()),
            array_flip(['lastmod', 'changefreq', 'priority'])
        );

        $html = '<' . $element . $this->htmlAttribs($prefix, $attribs) . '>';

        if (true === $escapeLabel) {
            $label = ($this->escaper)($label);
        }

        return $html . $label . '</' . $element . '>';
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * Overloads {@link \Laminas\View\Helper\AbstractHtmlElement::htmlAttribs()}.
     *
     * @param string $prefix
     * @param array  $attribs an array where each key-value pair is converted
     *                        to an attribute name and value
     *
     * @return string
     */
    private function htmlAttribs(string $prefix, array $attribs): string
    {
        // filter out null values and empty string values
        foreach ($attribs as $key => $value) {
            if (null !== $value && (!is_string($value) || mb_strlen($value))) {
                continue;
            }

            unset($attribs[$key]);
        }

        $xhtml = '';

        foreach ($attribs as $key => $val) {
            $key = ($this->escaper)($key);

            if (0 === mb_strpos($key, 'on') || ('constraints' === $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (!is_scalar($val)) {
                    // non-scalar data should be cast to JSON first
                    $val = Json::encode($val);
                }
            } else {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
            }

            $val = ($this->escapeHtmlAttr)($val);

            if ('id' === $key) {
                $val = $this->normalizeId($prefix, $val);
            }

            if (false !== mb_strpos($val, '"')) {
                $xhtml .= sprintf(' %s=\'%s\'', $key, $val);
            } else {
                $xhtml .= sprintf(' %s="%s"', $key, $val);
            }
        }

        return $xhtml;
    }

    /**
     * Normalize an ID
     *
     * @param string $prefix
     * @param string $value
     *
     * @return string
     */
    private function normalizeId(string $prefix, string $value): string
    {
        $prefix = mb_strtolower(trim(mb_substr($prefix, (int) mb_strrpos($prefix, '\\')), '\\'));

        return $prefix . '-' . $value;
    }
}
