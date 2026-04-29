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

interface HtmlElementInterface
{
    /**
     * Returns an HTML string
     *
     * @phpstan-param iterable<string, array<mixed>|bool|float|int|string|null> $attribs
     *
     * @return string HTML string
     *
     * @throws void
     */
    public function toHtml(string $element, iterable $attribs, string $content): string;
}
