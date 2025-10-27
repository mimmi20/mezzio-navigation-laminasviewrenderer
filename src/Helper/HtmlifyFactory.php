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

namespace Mimmi20\Mezzio\Navigation\LaminasView\Helper;

use Laminas\I18n\View\Helper\Translate;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;

final class HtmlifyFactory implements FactoryInterface
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param string            $requestedName
     * @param array<mixed>|null $options
     *
     * @throws ContainerExceptionInterface
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): Htmlify
    {
        $plugin     = $container->get(ViewHelperPluginManager::class);
        $translator = null;

        assert($plugin instanceof ViewHelperPluginManager);

        if ($plugin->has(Translate::class)) {
            $translator = $plugin->get(Translate::class);

            assert($translator instanceof Translate);
        }

        $escaper = $plugin->get(EscapeHtml::class);
        $element = $container->get(HtmlElementInterface::class);

        assert($escaper instanceof EscapeHtml);
        assert($element instanceof HtmlElementInterface);

        return new Htmlify($escaper, $element, $translator);
    }
}
