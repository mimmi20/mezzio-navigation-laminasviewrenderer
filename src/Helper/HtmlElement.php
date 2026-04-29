<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\Mezzio\Navigation\LaminasView\Helper;

use JsonException;
use Laminas\View\Helper\HtmlAttributes;
use Override;

use function sprintf;

final readonly class HtmlElement implements HtmlElementInterface
{
    /** @throws void */
    public function __construct(private HtmlAttributes $htmlAttributes)
    {
        // nothing to do
    }

    /**
     * Returns an HTML string
     *
     * @phpstan-param iterable<string, array<mixed>|bool|float|int|string|null> $attribs
     *
     * @return string HTML string
     *
     * @throws JsonException
     */
    #[Override]
    public function toHtml(string $element, iterable $attribs, string $content): string
    {
        return $this->open($element, $attribs) . $content . $this->close($element);
    }

    /**
     * Generate an opening tag
     *
     * @phpstan-param iterable<string, array<mixed>|bool|float|int|string|null> $attribs
     *
     * @throws JsonException
     */
    private function open(string $element, iterable $attribs): string
    {
        return sprintf('<%s%s>', $element, ($this->htmlAttributes)($attribs));
    }

    /**
     * Return a closing tag
     *
     * @throws void
     */
    private function close(string $element): string
    {
        return sprintf('</%s>', $element);
    }
}
