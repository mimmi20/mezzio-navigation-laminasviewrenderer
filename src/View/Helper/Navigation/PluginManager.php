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
namespace Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;

/**
 * Plugin manager implementation for navigation helpers
 *
 * Enforces that helpers retrieved are instances of
 * Navigation\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
final class PluginManager extends ViewHelperPluginManager implements ContainerInterface
{
    /** @var string Valid instance types. */
    protected $instanceOf = ViewHelperInterface::class;

    /**
     * Default aliases
     *
     * @var string[]
     */
    protected $aliases = [
        'breadcrumbs' => Breadcrumbs::class,
        'links' => Links::class,
        'menu' => Menu::class,
        'sitemap' => Sitemap::class,
    ];

    /**
     * Default factories
     *
     * @var string[]
     */
    protected $factories = [
        Breadcrumbs::class => BreadcrumbsFactory::class,
        Links::class => LinksFactory::class,
        Menu::class => MenuFactory::class,
        Sitemap::class => SitemapFactory::class,
    ];

    /**
     * Constructor
     *
     * Merges provided configuration with default configuration.
     *
     * Adds initializers to inject the attached renderer and translator, if
     * any, to the currently requested helper.
     *
     * @param ConfigInterface|ContainerInterface|null $configOrContainerInstance
     * @param array                                   $v3config                  if $configOrContainerInstance is a container, this
     *                                                                           value will be passed to the parent constructor
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        $v3config = array_merge_recursive(
            [
                'aliases' => $this->aliases,
                'factories' => $this->factories,
            ],
            $v3config
        );

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
