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
namespace Mezzio\Navigation\LaminasView\View\Helper\Navigation;

use Laminas\Permissions\Acl;
use Laminas\View\Helper\HelperInterface as BaseHelperInterface;
use Mezzio\Navigation;

/**
 * Interface for navigational helpers
 */
interface HelperInterface extends BaseHelperInterface
{
    /**
     * Magic overload: Should proxy to {@link render()}.
     *
     * @return string
     */
    public function __toString();

    /**
     * Renders helper
     *
     * @param Navigation\AbstractContainer|string|null $container [optional] container to render.
     *                                                            Default is null, which indicates
     *                                                            that the helper should render
     *                                                            the container returned by {@link *                                         getContainer()}.
     *
     * @throws \Laminas\View\Exception\ExceptionInterface
     *
     * @return string helper output
     */
    public function render($container = null): string;

    /**
     * Sets ACL to use when iterating pages
     *
     * @param Acl\AclInterface|null $acl [optional] ACL instance
     *
     * @return void
     */
    public function setAcl(?Acl\AclInterface $acl = null): void;

    /**
     * Returns ACL or null if it isn't set using {@link setAcl()} or
     * {@link setDefaultAcl()}
     *
     * @return Acl\AclInterface|null
     */
    public function getAcl();

    /**
     * Checks if the helper has an ACL instance
     *
     * @return bool
     */
    public function hasAcl(): bool;

    /**
     * Sets navigation container the helper should operate on by default
     *
     * @param Navigation\AbstractContainer|string|null $container [optional] container to operate
     *                                                            on. Default is null, which
     *                                                            indicates that the container
     *                                                            should be reset.
     *
     * @return void
     */
    public function setContainer($container = null): void;

    /**
     * Returns the navigation container the helper operates on by default
     *
     * @return Navigation\AbstractContainer navigation container
     */
    public function getContainer(): Navigation\AbstractContainer;

    /**
     * Checks if the helper has a container
     *
     * @return bool
     */
    public function hasContainer(): bool;

    /**
     * Render invisible items?
     *
     * @param bool $renderInvisible [optional] boolean flag
     *
     * @return void
     */
    public function setRenderInvisible(bool $renderInvisible = true): void;

    /**
     * Return renderInvisible flag
     *
     * @return bool
     */
    public function getRenderInvisible(): bool;

    /**
     * Sets ACL role to use when iterating pages
     *
     * @param mixed $role [optional] role to set.  Expects a string, an
     *                    instance of type {@link Acl\Role}, or null. Default
     *                    is null.
     *
     * @throws \Laminas\View\Exception\ExceptionInterface if $role is invalid
     *
     * @return void
     */
    public function setRole($role = null): void;

    /**
     * Returns ACL role to use when iterating pages, or null if it isn't set
     *
     * @return Acl\Role\RoleInterface|string|null
     */
    public function getRole();

    /**
     * Checks if the helper has an ACL role
     *
     * @return bool
     */
    public function hasRole(): bool;

    /**
     * Sets whether ACL should be used
     *
     * @param bool $useAcl [optional] whether ACL should be used. Default is true.
     *
     * @return void
     */
    public function setUseAcl(bool $useAcl = true): void;

    /**
     * Returns whether ACL should be used
     *
     * @return bool
     */
    public function getUseAcl(): bool;
}
