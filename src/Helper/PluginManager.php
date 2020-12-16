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
namespace Mezzio\Navigation\LaminasView\Helper;

use Interop\Container\ContainerInterface as InteropContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for navigation helpers
 *
 * Enforces that helpers retrieved are instances of
 * Navigation\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
final class PluginManager extends AbstractPluginManager implements InteropContainerInterface
{
    /** @var string Valid instance types. */
    protected $instanceOf = HelperInterface::class;

    /**
     * Default factories
     *
     * @var string[]
     */
    protected $factories = [
        AcceptHelperInterface::class => AcceptHelperFactory::class,
        ContainerParserInterface::class => ContainerParserFactory::class,
        FindActive::class => FindActiveFactory::class,
        FindRoot::class => InvokableFactory::class,
        HtmlifyInterface::class => HtmlifyFactory::class,
    ];

    protected $aliases = [
        FindRootInterface::class => FindRoot::class,
    ];
}
