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

use Laminas\Stdlib\Exception\InvalidArgumentException;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;

interface ConvertToPagesInterface
{
    /**
     * Converts a $mixed value to an array of pages
     *
     * @param ContainerInterface<PageInterface>|int|iterable<iterable<string>|string>|PageInterface|string $mixed     mixed value to get page(s) from
     * @param bool                                                                                         $recursive whether $value should be looped if it is an array or a config
     *
     * @return array<int, PageInterface>
     *
     * @throws InvalidArgumentException
     */
    public function convert(
        iterable | ContainerInterface | int | PageInterface | string $mixed,
        bool $recursive = true,
    ): array;
}
