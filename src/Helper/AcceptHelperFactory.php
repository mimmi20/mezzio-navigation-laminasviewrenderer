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

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;

final class AcceptHelperFactory implements FactoryInterface
{
    /**
     * Create and return a navigation view helper instance.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return AcceptHelper
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AcceptHelper
    {
        $authorization   = null;
        $renderInvisible = false;
        $role            = null;

        if (is_array($options)) {
            if (
                array_key_exists('authorization', $options)
                && $options['authorization'] instanceof AuthorizationInterface
            ) {
                $authorization = $options['authorization'];
            }

            if (array_key_exists('renderInvisible', $options)) {
                $renderInvisible = (bool) $options['renderInvisible'];
            }

            if (
                array_key_exists('role', $options)
                && is_string($options['role'])
            ) {
                $role = $options['role'];
            }
        }

        return new AcceptHelper($authorization, $renderInvisible, $role);
    }
}
