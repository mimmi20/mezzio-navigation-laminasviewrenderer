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

namespace Mimmi20\Mezzio\Navigation\LaminasView;

use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\Helper\UrlHelper as BaseUrlHelper;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\LaminasView\UrlHelper;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\NavigationFactory;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\ServerUrlHelperFactory;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\UrlHelperFactory;

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array<string, array<string, array<string, string>>>
     * @phpstan-return array{view_helpers: array{aliases: array<string, class-string>, factories: array<class-string, class-string>}, dependencies: array{factories: array<class-string, class-string>}}
     *
     * @throws void
     */
    public function __invoke(): array
    {
        return [
            'view_helpers' => $this->getViewHelperConfig(),
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array<string, array<string, string>>
     * @phpstan-return array{aliases: array<string, class-string>, factories: array<class-string, class-string>}
     *
     * @throws void
     */
    public function getViewHelperConfig(): array
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
     * @return array<string, array<string, string>>
     * @phpstan-return array{factories: array<class-string, class-string>}
     *
     * @throws void
     */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                Navigation\PluginManager::class => Navigation\PluginManagerFactory::class,
            ],
        ];
    }
}
