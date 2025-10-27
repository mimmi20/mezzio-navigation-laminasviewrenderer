<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\Mezzio\Navigation\LaminasView\Helper;

use Laminas\Stdlib\Exception\InvalidArgumentException;
use Mimmi20\Mezzio\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;

interface ContainerParserInterface
{
    /**
     * Verifies container and eventually fetches it from service locator if it is a string
     *
     * @param int|Navigation\ContainerInterface<PageInterface>|string|null $container
     *
     * @return Navigation\ContainerInterface<PageInterface>|null
     *
     * @throws InvalidArgumentException
     */
    public function parseContainer(
        int | Navigation\ContainerInterface | string | null $container = null,
    ): Navigation\ContainerInterface | null;
}
