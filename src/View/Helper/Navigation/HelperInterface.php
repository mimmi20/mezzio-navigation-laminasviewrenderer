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

use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\View\Helper\HelperInterface as BaseHelperInterface;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation;
use Mezzio\Navigation\ContainerInterface;

/**
 * Interface for navigational helpers
 */
interface HelperInterface extends BaseHelperInterface, TranslatorAwareInterface
{
    /**
     * Magic overload: Should proxy to {@link render()}.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Helper entry point
     *
     * @param Navigation\ContainerInterface|string|null $container container to operate on
     *
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return self
     */
    public function __invoke($container = null);

    /**
     * Renders helper
     *
     * @param Navigation\ContainerInterface|null $container [optional] container to render.
     *                                                      Default is null, which indicates
     *                                                      that the helper should render
     *                                                      the container returned by {@link getContainer()}.
     *
     * @throws \Laminas\View\Exception\ExceptionInterface
     *
     * @return string helper output
     */
    public function render(?ContainerInterface $container = null): string;

    /**
     * Sets Authorization to use when iterating pages
     *
     * @param AuthorizationInterface|null $authorization [optional] AuthorizationInterface instance
     *
     * @return void
     */
    public function setAuthorization(?AuthorizationInterface $authorization = null): void;

    /**
     * Returns Authorization or null if it isn't set using {@link setAuthorization()} or
     * {@link setDefaultAuthorization()}
     *
     * @return AuthorizationInterface|null
     */
    public function getAuthorization(): ?AuthorizationInterface;

    /**
     * Checks if the helper has an AuthorizationInterface instance
     *
     * @return bool
     */
    public function hasAuthorization(): bool;

    /**
     * Sets navigation container the helper operates on by default
     *
     * @param Navigation\ContainerInterface|string|null $container default is null, meaning container will be reset
     *
     * @return AbstractHelper
     */
    public function setContainer($container = null);

    /**
     * Returns the navigation container the helper operates on by default
     *
     * @return Navigation\ContainerInterface|null navigation container
     */
    public function getContainer(): ?Navigation\ContainerInterface;

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
     * Sets Authorization role to use when iterating pages
     *
     * @param string $role [optional] role to set.  Expects a string or null. Default is null.
     *
     * @throws \Laminas\View\Exception\ExceptionInterface if $role is invalid
     *
     * @return void
     */
    public function setRole(string $role): void;

    /**
     * Returns Authorization role to use when iterating pages, or null if it isn't set
     *
     * @return string|null
     */
    public function getRole(): ?string;

    /**
     * Checks if the helper has an Authorization role
     *
     * @return bool
     */
    public function hasRole(): bool;

    /**
     * Sets whether Authorization should be used
     *
     * @param bool $useAuthorization [optional] whether Authorization should be used. Default is true.
     *
     * @return void
     */
    public function setUseAuthorization(bool $useAuthorization = true): void;

    /**
     * Returns whether Authorization should be used
     *
     * @return bool
     */
    public function getUseAuthorization(): bool;
}
