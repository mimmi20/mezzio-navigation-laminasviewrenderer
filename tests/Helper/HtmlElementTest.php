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

namespace Mimmi20Test\LaminasView\Helper\HtmlElement\Helper;

use JsonException;
use Laminas\View\Helper\HtmlAttributes;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\HtmlElement;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

final class HtmlElementTest extends TestCase
{
    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testToHtml(): void
    {
        $expected = '<a id="testId" class="test-class" href="&#x23;" target="_blank" onClick="&#x7B;&quot;a&quot;&#x3A;&quot;b&quot;&#x7D;" data-test="test-class1&#x20;test-class2">testLabelTranslatedAndEscaped</a>';

        $escapedTranslatedLabel = 'testLabelTranslatedAndEscaped';
        $id                     = 'testId';
        $class                  = 'test-class';
        $href                   = '#';
        $target                 = '_blank';
        $onclick                = ['a' => 'b'];
        $testData               = ['test-class1', 'test-class2'];

        $htmlAttributes = new HtmlAttributes();

        $element = 'a';

        $htmlElement = new HtmlElement($htmlAttributes);

        self::assertSame(
            $expected,
            $htmlElement->toHtml(
                $element,
                ['id' => $id, 'class' => $class, 'href' => $href, 'target' => $target, 'onClick' => $onclick, 'data-test' => $testData],
                $escapedTranslatedLabel,
            ),
        );
    }
}
