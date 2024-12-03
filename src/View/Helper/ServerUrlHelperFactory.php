<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\Mezzio\Navigation\LaminasView\View\Helper;

use Mezzio\Helper\Exception\MissingHelperException;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\LaminasView\ServerUrlHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function sprintf;

final class ServerUrlHelperFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @throws ContainerExceptionInterface
     * @throws MissingHelperException
     */
    public function __invoke(ContainerInterface $container): ServerUrlHelper
    {
        if (!$container->has(BaseServerUrlHelper::class)) {
            throw new MissingHelperException(
                sprintf(
                    'An instance of %s is required in order to create the "url" view helper; not found',
                    BaseServerUrlHelper::class,
                ),
            );
        }

        $baseUrl = $container->get(BaseServerUrlHelper::class);

        assert($baseUrl instanceof BaseServerUrlHelper);

        return new ServerUrlHelper($baseUrl);
    }
}
