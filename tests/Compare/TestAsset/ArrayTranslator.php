<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

/** @see       https://github.com/laminas/laminas-view for the canonical source repository */

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Compare\TestAsset;

use Laminas\I18n\Translator;
use Laminas\I18n\Translator\TextDomain;

/** phpcs:disable SlevomatCodingStandard.Classes.ForbiddenPublicProperty.ForbiddenPublicProperty */
final class ArrayTranslator implements Translator\Loader\FileLoaderInterface
{
    /** @var array<string> */
    public array $translations;

    /**
     * Load translations from a file.
     *
     * @param string $locale
     * @param string $filename
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function load($filename, $locale): TextDomain | null
    {
        return new Translator\TextDomain($this->translations);
    }
}
