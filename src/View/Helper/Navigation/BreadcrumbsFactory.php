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
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Navigation\LaminasView\Helper\ContainerParserInterface;
use Mezzio\Navigation\LaminasView\Helper\HtmlifyInterface;
use Mezzio\Navigation\LaminasView\Helper\PluginManager as HelperPluginManager;

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
        $helperPluginManager = $container->get(HelperPluginManager::class);
        $plugin              = $container->get(ViewHelperPluginManager::class);
        $translator          = null;

        if ($plugin->has(Translate::class)) {
            $translator = $plugin->get(Translate::class);
        }

        return new Breadcrumbs(
            $container,
            $container->get(Logger::class),
            $helperPluginManager->get(HtmlifyInterface::class),
            $helperPluginManager->get(ContainerParserInterface::class),
            $plugin->get(EscapeHtml::class),
            $plugin->get(Partial::class),
            $translator
        );
    }
}
