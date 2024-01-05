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

namespace Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Laminas\View\Helper\AbstractHtmlElement;

use function implode;

/**
 * Helper for printing breadcrumbs.
 *
 * phpcs:disable SlevomatCodingStandard.Classes.TraitUseDeclaration.MultipleTraitsPerDeclaration
 */
final class Breadcrumbs extends AbstractHtmlElement implements BreadcrumbsInterface
{
    use BreadcrumbsTrait, HelperTrait{
        BreadcrumbsTrait::getMinDepth insteadof HelperTrait;
    }

    /**
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function renderBreadcrumbItem(string $html, string $liClass = '', bool $active = false): string
    {
        return $html;
    }

    /** @throws void */
    private function renderSeparator(): string
    {
        return $this->getSeparator();
    }

    /**
     * @param array<string> $html
     *
     * @throws void
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function combineRendered(array $html): string
    {
        return $html !== [] ? $this->getIndent() . implode($this->renderSeparator(), $html) : '';
    }
}
