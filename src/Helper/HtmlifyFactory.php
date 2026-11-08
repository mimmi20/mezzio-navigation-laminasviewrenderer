<?php

/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\Mezzio\Navigation\LaminasView\Helper;

use Laminas\Escaper\Escaper;
use Laminas\Escaper\EscaperInterface;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\View\Helper\Translate;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Translator\TranslatorInterface;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
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
    public function __invoke(ContainerInterface $container, string $requestedName, array | null $options = null): Htmlify
    {
        $translator = null;

        if ($container->has(TranslatorInterface::class)) {
            $translator = $container->get(TranslatorInterface::class);

            assert($translator instanceof TranslatorInterface);
        }

        $escaper = $container->get(EscaperInterface::class);
        $element = $container->get(HtmlElementInterface::class);

        assert($escaper instanceof EscaperInterface);
        assert($element instanceof HtmlElementInterface);

        return new Htmlify($escaper, $element, $translator);
    }
}
