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

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Helper;

use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ContainerParser;
use Mimmi20\Mezzio\Navigation\LaminasView\Helper\ContainerParserFactory;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function assert;

final class ContainerParserFactoryTest extends TestCase
{
    /** @throws Exception */
    public function testInvocation(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('get');

        assert($container instanceof ContainerInterface);
        $helper = (new ContainerParserFactory())($container, '');

        self::assertInstanceOf(ContainerParser::class, $helper);
    }
}
