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

use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\Helper\UrlHelper as BaseUrlHelper;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\LaminasView\UrlHelper;
use Mezzio\Navigation\LaminasView\Helper\PluginManager;
use Mezzio\Navigation\LaminasView\Helper\PluginManagerFactory;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mezzio\Navigation\LaminasView\View\Helper\NavigationFactory;
use Mezzio\Navigation\LaminasView\View\Helper\ServerUrlHelperFactory;
use Mezzio\Navigation\LaminasView\View\Helper\UrlHelperFactory;

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
            'dependencies' => $this->getDependencyConfig(),
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
            'aliases' => [
                'navigation' => Navigation::class,
                'Navigation' => Navigation::class,
                BaseServerUrlHelper::class => ServerUrlHelper::class,
                'serverurl' => ServerUrlHelper::class,
                'serverUrl' => ServerUrlHelper::class,
                'ServerUrl' => ServerUrlHelper::class,
                BaseUrlHelper::class => UrlHelper::class,
                'url' => UrlHelper::class,
                'Url' => UrlHelper::class,
            ],
            'factories' => [
                Navigation::class => NavigationFactory::class,
                UrlHelper::class => UrlHelperFactory::class,
                ServerUrlHelper::class => ServerUrlHelperFactory::class,
            ],
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'factories' => [
                PluginManager::class => PluginManagerFactory::class,
            ],
        ];
    }
}
