<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace Mezzio\Navigation\LaminasView\Helper;

use Mezzio\Navigation\ContainerInterface;

interface FindActiveInterface extends HelperInterface
{
    /**
     * Finds the deepest active page in the given container
     *
     * @param ContainerInterface $container to search
     * @param int|null           $minDepth  [optional] minimum depth
     *                                      required for page to be
     *                                      valid. Default is to use
     *                                      {@link getMinDepth()}. A
     *                                      null value means no minimum
     *                                      depth required.
     * @param int|null           $maxDepth  [optional] maximum depth
     *                                      a page can have to be
     *                                      valid. Default is to use
     *                                      {@link getMaxDepth()}. A
     *                                      null value means no maximum
     *                                      depth required.
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws \Mezzio\Navigation\Exception\InvalidArgumentException
     *
     * @return array an associative array with the values 'depth' and 'page',
     *               or an empty array if not found
     */
    public function find(ContainerInterface $container, ?int $minDepth, ?int $maxDepth): array;
}
