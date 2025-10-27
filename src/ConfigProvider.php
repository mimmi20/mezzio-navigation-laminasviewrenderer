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

namespace Mimmi20\Mezzio\Navigation\LaminasView;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\Helper\UrlHelper as BaseUrlHelper;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\LaminasView\UrlHelper;

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array<string, array<string, array<string, string>>>
     * @phpstan-return array{view_helpers: array{aliases: array<string, class-string>, factories: array<class-string, class-string>}, dependencies: array{factories: array<class-string, class-string>, aliases: array<class-string, class-string>}}
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
                'navigation' => View\Helper\Navigation::class,
                'Navigation' => View\Helper\Navigation::class,
                BaseServerUrlHelper::class => ServerUrlHelper::class,
                'serverurl' => ServerUrlHelper::class,
                'serverUrl' => ServerUrlHelper::class,
                'ServerUrl' => ServerUrlHelper::class,
                BaseUrlHelper::class => UrlHelper::class,
                'url' => UrlHelper::class,
                'Url' => UrlHelper::class,
            ],
            'factories' => [
                View\Helper\Navigation::class => View\Helper\NavigationFactory::class,
                UrlHelper::class => View\Helper\UrlHelperFactory::class,
                ServerUrlHelper::class => View\Helper\ServerUrlHelperFactory::class,
            ],
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array<string, array<string, string>>
     * @phpstan-return array{factories: array<class-string, class-string>, aliases: array<class-string, class-string>}
     *
     * @throws void
     */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                View\Helper\Navigation\PluginManager::class => View\Helper\Navigation\PluginManagerFactory::class,
                Helper\ContainerParser::class => Helper\ContainerParserFactory::class,
                Helper\FindRoot::class => InvokableFactory::class,
                Helper\Htmlify::class => Helper\HtmlifyFactory::class,
            ],
            'aliases' => [
                Helper\ContainerParserInterface::class => Helper\ContainerParser::class,
                Helper\FindRootInterface::class => Helper\FindRoot::class,
                Helper\HtmlifyInterface::class => Helper\Htmlify::class,
            ],
        ];
    }
}
