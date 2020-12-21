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

use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Page\PageInterface;
use Traversable;

interface ConvertToPagesInterface extends HelperInterface
{
    /**
     * Converts a $mixed value to an array of pages
     *
     * @param ContainerInterface|PageInterface|string|Traversable $mixed     mixed value to get page(s) from
     * @param bool                                                $recursive whether $value should be looped if it is an array or a config
     *
     * @return PageInterface[]
     */
    public function convert($mixed, bool $recursive = true): array;
}
