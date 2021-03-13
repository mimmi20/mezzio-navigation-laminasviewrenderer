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

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 */
namespace MezzioTest\Navigation\LaminasView\Compare\TestAsset;

use Laminas\I18n\Translator;
use Laminas\I18n\Translator\TextDomain;

final class ArrayTranslator implements Translator\Loader\FileLoaderInterface
{
    /** @var array */
    public $translations;

    /**
     * Load translations from a file.
     *
     * @param string $locale
     * @param string $filename
     *
     * @return TextDomain|null
     */
    public function load($filename, $locale): ?TextDomain
    {
        return new Translator\TextDomain($this->translations);
    }
}
