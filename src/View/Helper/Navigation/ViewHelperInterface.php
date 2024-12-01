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

use Laminas\Stdlib\Exception\DomainException;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Laminas\View\Exception;
use Laminas\View\Helper\HelperInterface;
use Mimmi20\Mezzio\GenericAuthorization\AuthorizationInterface;
use Mimmi20\Mezzio\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Override;
use Stringable;

/**
 * Interface for navigational helpers
 */
interface ViewHelperInterface extends HelperInterface, Stringable
{
    /**
     * Magic overload: Should proxy to {@link render()}.
     *
     * @throws void
     */
    #[Override]
    public function __toString(): string;

    /**
     * Helper entry point
     *
     * @param Navigation\ContainerInterface<PageInterface>|string|null $container container to operate on
     *
     * @return self
     *
     * @throws Exception\InvalidArgumentException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function __invoke(Navigation\ContainerInterface | string | null $container = null);

    /**
     * Renders helper
     *
     * @param Navigation\ContainerInterface<PageInterface>|string|null $container [optional] container to render. Default is null, which indicates that the helper should render the container returned by {@link getContainer()}.
     *
     * @return string helper output
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function render(Navigation\ContainerInterface | string | null $container = null): string;

    /**
     * Sets Authorization to use when iterating pages
     *
     * @param AuthorizationInterface|null $authorization [optional] AuthorizationInterface instance
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setAuthorization(AuthorizationInterface | null $authorization = null);

    /**
     * Returns Authorization or null if it isn't set using {@link setAuthorization()} or
     * {@link setDefaultAuthorization()}
     *
     * @throws void
     */
    public function getAuthorization(): AuthorizationInterface | null;

    /**
     * Checks if the helper has an AuthorizationInterface instance
     *
     * @throws void
     */
    public function hasAuthorization(): bool;

    /**
     * Sets navigation container the helper operates on by default
     *
     * @param Navigation\ContainerInterface<PageInterface>|string|null $container default is null, meaning container will be reset
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setContainer(Navigation\ContainerInterface | string | null $container = null);

    /**
     * Returns the navigation container the helper operates on by default
     *
     * @return Navigation\ContainerInterface<PageInterface>|null navigation container
     *
     * @throws void
     */
    public function getContainer(): Navigation\ContainerInterface | null;

    /**
     * Checks if the helper has a container
     *
     * @throws void
     */
    public function hasContainer(): bool;

    /**
     * Set the indentation string for using in {@link render()}, optionally a
     * number of spaces to indent with
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setIndent(int | string $indent);

    /**
     * Returns indentation
     *
     * @throws void
     */
    public function getIndent(): string;

    /**
     * Sets the maximum depth a page can have to be included when rendering
     *
     * @param int $maxDepth default is null, which sets no maximum depth
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setMaxDepth(int $maxDepth);

    /**
     * Sets the minimum depth a page must have to be included when rendering
     *
     * @param int $minDepth default is null, which sets no minimum depth
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setMinDepth(int $minDepth);

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @throws void
     */
    public function getMinDepth(): int | null;

    /**
     * Returns maximum depth a page can have to be included when rendering
     *
     * @throws void
     */
    public function getMaxDepth(): int | null;

    /**
     * Render invisible items?
     *
     * @param bool $renderInvisible [optional] boolean flag
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setRenderInvisible(bool $renderInvisible = true);

    /**
     * Return renderInvisible flag
     *
     * @throws void
     */
    public function getRenderInvisible(): bool;

    /**
     * Sets Authorization role to use when iterating pages
     *
     * @param string $role [optional] role to set.  Expects a string or null. Default is null.
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setRole(string $role);

    /**
     * Returns Authorization role to use when iterating pages, or null if it isn't set
     *
     * @throws void
     */
    public function getRole(): string | null;

    /**
     * Checks if the helper has an Authorization role
     *
     * @throws void
     */
    public function hasRole(): bool;

    /**
     * Sets whether Authorization should be used
     *
     * @param bool $useAuthorization [optional] whether Authorization should be used. Default is true.
     *
     * @return self
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function setUseAuthorization(bool $useAuthorization = true);

    /**
     * Returns whether Authorization should be used
     *
     * @throws void
     */
    public function getUseAuthorization(): bool;
}
