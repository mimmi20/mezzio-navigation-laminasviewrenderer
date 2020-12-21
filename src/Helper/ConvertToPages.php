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

use Laminas\Log\Logger;
use Laminas\Stdlib\ArrayUtils;
use Mezzio\Navigation\ContainerInterface;
use Mezzio\Navigation\Exception\InvalidArgumentException;
use Mezzio\Navigation\Page\PageFactoryInterface;
use Mezzio\Navigation\Page\PageInterface;
use Traversable;

final class ConvertToPages implements ConvertToPagesInterface
{
    /** @var \Laminas\Log\Logger */
    private $logger;

    /** @var PageFactoryInterface */
    private $pageFactory;

    /**
     * @param Logger               $logger
     * @param PageFactoryInterface $pageFactory
     */
    public function __construct(Logger $logger, PageFactoryInterface $pageFactory)
    {
        $this->logger      = $logger;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Converts a $mixed value to an array of pages
     *
     * @param ContainerInterface|PageInterface|string|Traversable $mixed     mixed value to get page(s) from
     * @param bool                                                $recursive whether $value should be looped if it is an array or a config
     *
     * @return PageInterface[]
     */
    public function convert($mixed, bool $recursive = true): array
    {
        if ($mixed instanceof PageInterface) {
            // value is a page instance; return directly
            return [$mixed];
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
                $page = $this->pageFactory->factory(
                    [
                        'type' => 'uri',
                        'uri' => $mixed,
                    ]
                );

                return [$page];
            } catch (InvalidArgumentException $e) {
                $this->logger->err($e);

                return [];
            }
        }

        if ($mixed instanceof Traversable) {
            try {
                $mixed = ArrayUtils::iteratorToArray($mixed);
            } catch (\Laminas\Stdlib\Exception\InvalidArgumentException $e) {
                $this->logger->err($e);

                return [];
            }
        }

        if (is_array($mixed) && [] !== $mixed) {
            if ($recursive && is_numeric(key($mixed))) {
                // first key is numeric; assume several pages
                return array_map(
                    function ($value): PageInterface {
                        [$page] = $this->convert($value, false);

                        return $page;
                    },
                    $mixed
                );
            }

            // pass array to factory directly
            try {
                $page = $this->pageFactory->factory($mixed);

                return [$page];
            } catch (InvalidArgumentException $e) {
                $this->logger->err($e);
            }
        }

        // nothing found
        return [];
    }
}
