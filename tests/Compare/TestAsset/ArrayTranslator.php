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

/** @see       https://github.com/laminas/laminas-view for the canonical source repository */

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Compare\TestAsset;

use Laminas\I18n\Translator;
use Override;

final readonly class ArrayTranslator implements Translator\Loader\FileLoaderInterface
{
    /**
     * @param array<string, string> $translations
     *
     * @throws void
     */
    public function __construct(private array $translations = [])
    {
        // nothing to do
    }

    /**
     * Load translations from a file.
     *
     * @param string $locale
     * @param string $filename
     *
     * @return Translator\TextDomain<string, string>
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function load($filename, $locale): Translator\TextDomain
    {
        return new Translator\TextDomain($this->translations);
    }
}
