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

use Laminas\View\Exception;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation;

/**
 * Interface for navigational helpers
 */
interface ViewHelperInterface
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
     * @throws Exception\InvalidArgumentException
     *
     * @return self
     */
    public function __invoke($container = null);

    /**
     * Renders helper
     *
     * @param Navigation\ContainerInterface|string|null $container [optional] container to render.
     *                                                             Default is null, which indicates
     *                                                             that the helper should render
     *                                                             the container returned by {@link getContainer()}.
     *
     * @throws Exception\RuntimeException
     *
     * @return string helper output
     */
    public function render($container = null): string;

    /**
     * Sets Authorization to use when iterating pages
     *
     * @param AuthorizationInterface|null $authorization [optional] AuthorizationInterface instance
     *
     * @return self
     */
    public function setAuthorization(?AuthorizationInterface $authorization = null);

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
     * @return self
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
     * Set the indentation string for using in {@link render()}, optionally a
     * number of spaces to indent with
     *
     * @param int|string $indent
     *
     * @return self
     */
    public function setIndent($indent);

    /**
     * Returns indentation
     *
     * @return string
     */
    public function getIndent(): string;

    /**
     * Sets the maximum depth a page can have to be included when rendering
     *
     * @param int $maxDepth default is null, which sets no maximum depth
     *
     * @return self
     */
    public function setMaxDepth(int $maxDepth);

    /**
     * Sets the minimum depth a page must have to be included when rendering
     *
     * @param int $minDepth default is null, which sets no minimum depth
     *
     * @return self
     */
    public function setMinDepth(int $minDepth);

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @return int|null
     */
    public function getMinDepth(): ?int;

    /**
     * Returns maximum depth a page can have to be included when rendering
     *
     * @return int|null
     */
    public function getMaxDepth(): ?int;

    /**
     * Render invisible items?
     *
     * @param bool $renderInvisible [optional] boolean flag
     *
     * @return self
     */
    public function setRenderInvisible(bool $renderInvisible = true);

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
     * @return self
     */
    public function setRole(string $role);

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
     * @return self
     */
    public function setUseAuthorization(bool $useAuthorization = true);

    /**
     * Returns whether Authorization should be used
     *
     * @return bool
     */
    public function getUseAuthorization(): bool;
}
