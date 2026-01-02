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

use Laminas\Stdlib\ArrayUtils;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Exception\InvalidArgumentException;
use Mimmi20\Mezzio\Navigation\Page\PageFactoryInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Override;
use Traversable;

use function array_map;
use function assert;
use function is_array;
use function is_numeric;
use function is_string;
use function key;

final readonly class ConvertToPages implements ConvertToPagesInterface
{
    /** @throws void */
    public function __construct(private PageFactoryInterface | null $pageFactory)
    {
        // nothing to do
    }

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
    #[Override]
    public function convert(
        iterable | ContainerInterface | int | PageInterface | string $mixed,
        bool $recursive = true,
    ): array {
        if ($mixed instanceof PageInterface) {
            // value is a page instance; return directly
            return [$mixed];
        }

        if ($mixed instanceof ContainerInterface) {
            // value is a container; return pages in it
            $pages = [];

            foreach ($mixed as $page) {
                assert($page instanceof PageInterface);
                $pages[] = $page;
            }

            return $pages;
        }

        if (is_string($mixed)) {
            // value is a string; make a URI page
            $page = $this->pageFactory?->factory(
                [
                    'type' => 'uri',
                    'uri' => $mixed,
                ],
            );

            return $page === null ? [] : [$page];
        }

        if ($mixed instanceof Traversable) {
            try {
                $mixed = ArrayUtils::iteratorToArray($mixed);
            } catch (\Laminas\Stdlib\Exception\InvalidArgumentException $e) {
                throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
            }
        }

        if (!is_array($mixed) || $mixed === []) {
            // nothing found
            return [];
        }

        if ($recursive && is_numeric(key($mixed))) {
            // first key is numeric; assume several pages
            return array_map(
                /** @return PageInterface */
                function (iterable | ContainerInterface | int | PageInterface | string $value) {
                    [$page] = $this->convert($value, false);

                    return $page;
                },
                $mixed,
            );
        }

        // pass array to factory directly
        $page = $this->pageFactory?->factory($mixed);

        return $page === null ? [] : [$page];
    }
}
