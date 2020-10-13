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
namespace Mezzio\Navigation\LaminasView;

use Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mezzio\Navigation\LaminasView\View\Helper\NavigationFactory;

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'view_helpers' => $this->getViewHelperConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return [
            'delegators' => [
                'ViewHelperManager' => [
                    ViewHelperManagerDelegatorFactory::class,
                ],
            ],
            'aliases' => [
                'navigation' => Navigation::class,
                'Navigation' => Navigation::class,
            ],
            'factories' => [
                Navigation::class => NavigationFactory::class,
            ],
        ];
    }
}
