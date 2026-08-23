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

use Laminas\View\Helper\HtmlAttributes;
use Laminas\View\HelperPluginManager;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\HtmlElement;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\HtmlElementFactory;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;

final class HtmlElementFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testInvocation(): void
    {
        $htmlAttributes = self::createStub(HtmlAttributes::class);

        $helperPluginManager = $this->createMock(HelperPluginManager::class);
        $helperPluginManager->expects(self::once())
            ->method('get')
            ->with(HtmlAttributes::class, null)
            ->willReturn($htmlAttributes);
        $helperPluginManager->expects(self::never())
            ->method('has');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with(HelperPluginManager::class)
            ->willReturn($helperPluginManager);

        assert($container instanceof ContainerInterface);
        $helper = (new HtmlElementFactory())($container, '');

        self::assertInstanceOf(HtmlElement::class, $helper);
    }
}
