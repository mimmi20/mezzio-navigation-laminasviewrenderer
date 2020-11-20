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

use Interop\Container\ContainerInterface;
use Laminas\I18n\View\Helper\Translate;
use Laminas\Log\Logger;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\Partial;
use Laminas\View\HelperPluginManager;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;

final class BreadcrumbsFactory
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param ContainerInterface $container
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws ServiceNotCreatedException
     *
     * @return Breadcrumbs
     */
    public function __invoke(ContainerInterface $container): Breadcrumbs
    {
        $plugin     = $container->get(HelperPluginManager::class);
        $translator = null;

        if ($plugin->has(Translate::class)) {
            $translator = $plugin->get(Translate::class);
        }

        return new Breadcrumbs(
            $container,
            $container->get(Logger::class),
            $container->get(HtmlifyInterface::class),
            $plugin->get(EscapeHtml::class),
            $plugin->get(Partial::class),
            $translator
        );
    }
}
