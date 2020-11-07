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
namespace Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Interop\Container\ContainerInterface as InteropContainerInterface;
use Laminas\View\HelperPluginManager;

/**
 * Plugin manager implementation for navigation helpers
 *
 * Enforces that helpers retrieved are instances of
 * Navigation\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
final class PluginManager extends HelperPluginManager implements InteropContainerInterface
{
    /** @var string Valid instance types. */
    protected $instanceOf = AbstractHelper::class;

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
        Breadcrumbs::class => HelperFactory::class,
        Links::class => HelperFactory::class,
        Menu::class => HelperFactory::class,
        Sitemap::class => HelperFactory::class,
    ];
}
