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

use Laminas\View\Helper\AbstractHtmlElement;

/**
 * Helper for printing breadcrumbs.
 */
final class Breadcrumbs extends AbstractHtmlElement implements BreadcrumbsInterface
{
    use BreadcrumbsTrait, HelperTrait{
        BreadcrumbsTrait::getMinDepth insteadof HelperTrait;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    private function renderBreadcrumbItem(string $html, string $liClass = '', bool $active = false): string
    {
        return $html;
    }
}
