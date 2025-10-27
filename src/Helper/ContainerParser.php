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
use Mimmi20\Mezzio\Navigation\Navigation as MezzioNavigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function get_debug_type;
use function in_array;
use function is_string;
use function sprintf;

final readonly class ContainerParser implements ContainerParserInterface
{
    /** @throws void */
    public function __construct(private ContainerInterface $serviceLocator)
    {
        // nothing to do
    }

    /**
     * Verifies container and eventually fetches it from service locator if it is a string
     *
     * @param int|\Mimmi20\Mezzio\Navigation\ContainerInterface<PageInterface>|string|null $container
     *
     * @return \Mimmi20\Mezzio\Navigation\ContainerInterface<PageInterface>|null
     *
     * @throws InvalidArgumentException
     */
    #[Override]
    public function parseContainer(
        int | \Mimmi20\Mezzio\Navigation\ContainerInterface | string | null $container = null,
    ): \Mimmi20\Mezzio\Navigation\ContainerInterface | null {
        if (
            $container === null
            || $container instanceof \Mimmi20\Mezzio\Navigation\ContainerInterface
        ) {
            return $container;
        }

        if (is_string($container)) {
            // Fallback
            if (in_array($container, ['default', 'navigation'], true)) {
                // Uses class name
                if ($this->serviceLocator->has(MezzioNavigation::class)) {
                    try {
                        $container = $this->serviceLocator->get(MezzioNavigation::class);
                    } catch (ContainerExceptionInterface $e) {
                        throw new InvalidArgumentException(
                            sprintf('Could not load Container "%s"', MezzioNavigation::class),
                            0,
                            $e,
                        );
                    }

                    assert(
                        $container instanceof \Mimmi20\Mezzio\Navigation\ContainerInterface,
                        sprintf(
                            '$container should be an Instance of %s, but was %s',
                            \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
                            get_debug_type($container),
                        ),
                    );

                    return $container;
                }

                // Uses old service name
                if ($this->serviceLocator->has('navigation')) {
                    try {
                        $container = $this->serviceLocator->get('navigation');
                    } catch (ContainerExceptionInterface $e) {
                        throw new InvalidArgumentException(
                            'Could not load Container "navigation"',
                            0,
                            $e,
                        );
                    }

                    assert(
                        $container instanceof \Mimmi20\Mezzio\Navigation\ContainerInterface,
                        sprintf(
                            '$container should be an Instance of %s, but was %s',
                            \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
                            get_debug_type($container),
                        ),
                    );

                    return $container;
                }
            }

            /*
             * Load the navigation container from the root service locator
             */
            try {
                $container = $this->serviceLocator->get($container);
            } catch (ContainerExceptionInterface $e) {
                throw new InvalidArgumentException(
                    sprintf('Could not load Container "%s"', $container),
                    0,
                    $e,
                );
            }

            assert(
                $container instanceof \Mimmi20\Mezzio\Navigation\ContainerInterface,
                sprintf(
                    '$container should be an Instance of %s, but was %s',
                    \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
                    get_debug_type($container),
                ),
            );

            return $container;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Container must be a string alias or an instance of %s',
                \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
            ),
        );
    }
}
