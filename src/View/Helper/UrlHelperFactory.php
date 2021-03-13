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

final class UrlHelperFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param ContainerInterface $container
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     *
     * @return UrlHelper
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

        return new UrlHelper($container->get(BaseUrlHelper::class));
    }
}
