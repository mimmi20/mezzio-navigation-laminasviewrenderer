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

use Laminas\View\Helper\Navigation as NavigationHelper;
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
            'templates' => $this->getTemplates(),
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
                'navigation' => NavigationHelper::class,
                'Navigation' => NavigationHelper::class,
            ],
            'factories' => [
                NavigationHelper::class => NavigationFactory::class,
                'laminasviewhelpernavigation' => NavigationFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app' => ['templates/app'],
                'error' => ['templates/error'],
                'layout' => ['templates/layout'],
            ],
        ];
    }

    /**
     * Get view helper configuration
     *
     * @return array
     *
    public function getViewHelperConfig(): array
    {
        return [
            'aliases' => [
                'baseUrl' => BaseUrl::class,
                'revisionHeadLink' => RevisionHeadLink::class,
                'revisionInlineScript' => RevisionInlineScript::class,
                'revisionHeadScript' => RevisionHeadScript::class,
            ],
            'factories' => [
                BaseUrl::class => BaseUrlFactory::class,
                RevisionHeadLink::class => RevisionHeadLinkFactory::class,
                RevisionInlineScript::class => RevisionInlineScriptFactory::class,
                RevisionHeadScript::class => RevisionHeadScriptFactory::class,
            ],
        ];
    }
    /**/
}
