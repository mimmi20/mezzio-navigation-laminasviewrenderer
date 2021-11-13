<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mezzio\Navigation\LaminasView\View\Helper;

use Interop\Container\ContainerInterface;
use Mezzio\Helper\Exception\MissingHelperException;
use Mezzio\Helper\UrlHelper as BaseUrlHelper;
use Mezzio\LaminasView\UrlHelper;
use Psr\Container\ContainerExceptionInterface;

use function assert;
use function sprintf;

final class UrlHelperFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UrlHelper
    {
        if (!$container->has(BaseUrlHelper::class)) {
            throw new MissingHelperException(
                sprintf(
                    'An instance of %s is required in order to create the "url" view helper; not found',
                    BaseUrlHelper::class
                )
            );
        }

        $baseUrl = $container->get(BaseUrlHelper::class);

        assert($baseUrl instanceof BaseUrlHelper);

        return new UrlHelper($baseUrl);
    }
}
