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
namespace MezzioTest\Navigation\LaminasView\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Mezzio\GenericAuthorization\AuthorizationInterface;
use Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use PHPUnit\Framework\TestCase;

final class BreadcrumbsTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMaxDepth(): void
    {
        $maxDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertNull($helper->getMaxDepth());

        $helper->setMaxDepth($maxDepth);

        self::assertSame($maxDepth, $helper->getMaxDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetMinDepth(): void
    {
        $minDepth       = 4;
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertSame(1, $helper->getMinDepth());

        $helper->setMinDepth($minDepth);

        self::assertSame($minDepth, $helper->getMinDepth());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRenderInvisible(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertFalse($helper->getRenderInvisible());

        $helper->setRenderInvisible(true);

        self::assertTrue($helper->getRenderInvisible());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Laminas\View\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function testSetRole(): void
    {
        $role           = 'testRole';
        $defaultRole    = 'testDefaultRole';
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertNull($helper->getRole());
        self::assertFalse($helper->hasRole());

        Breadcrumbs::setDefaultRole($defaultRole);

        self::assertSame($defaultRole, $helper->getRole());
        self::assertTrue($helper->hasRole());

        $helper->setRole($role);

        self::assertSame($role, $helper->getRole());
        self::assertTrue($helper->hasRole());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetServiceLocator(): void
    {
        $logger          = $this->createMock(Logger::class);
        $serviceLocator1 = $this->createMock(ContainerInterface::class);
        $serviceLocator2 = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator1 */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator1, $logger);

        self::assertSame($serviceLocator1, $helper->getServiceLocator());

        /* @var ContainerInterface $serviceLocator2 */
        $helper->setServiceLocator($serviceLocator2);

        self::assertSame($serviceLocator2, $helper->getServiceLocator());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetUseAuthorization(): void
    {
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertTrue($helper->getUseAuthorization());

        $helper->setUseAuthorization(false);

        self::assertFalse($helper->getUseAuthorization());
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return void
     */
    public function testSetAuthorization(): void
    {
        $auth           = $this->createMock(AuthorizationInterface::class);
        $defaultAuth    = $this->createMock(AuthorizationInterface::class);
        $logger         = $this->createMock(Logger::class);
        $serviceLocator = $this->createMock(ContainerInterface::class);

        /** @var ContainerInterface $serviceLocator */
        /** @var Logger $logger */
        $helper = new Breadcrumbs($serviceLocator, $logger);

        self::assertNull($helper->getAuthorization());
        self::assertFalse($helper->hasAuthorization());

        /* @var AuthorizationInterface $defaultAuth */
        Breadcrumbs::setDefaultAuthorization($defaultAuth);

        self::assertSame($defaultAuth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());

        /* @var AuthorizationInterface $auth */
        $helper->setAuthorization($auth);

        self::assertSame($auth, $helper->getAuthorization());
        self::assertTrue($helper->hasAuthorization());
    }
}
