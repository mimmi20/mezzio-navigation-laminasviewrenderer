<?php
/**
 * This file is part of the mimmi20/mezzio-navigation-laminasviewrenderer package.
 *
 * Copyright (c) 2020-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Mezzio\Navigation\LaminasView\Compare;

use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\HelperPluginManager as ViewHelperPluginManager;
use Mezzio\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Mezzio\Helper\UrlHelper as BaseUrlHelper;
use Mezzio\LaminasView\HelperPluginManagerFactory;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\LaminasView\LaminasViewRendererFactory;
use Mezzio\LaminasView\ServerUrlHelper;
use Mezzio\LaminasView\UrlHelper;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementFactory;
use Mimmi20\LaminasView\Helper\HtmlElement\Helper\HtmlElementInterface;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererFactory;
use Mimmi20\LaminasView\Helper\PartialRenderer\Helper\PartialRendererInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\PluginManager;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\ViewHelperInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\NavigationFactory;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\ServerUrlHelperFactory;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\UrlHelperFactory;
use Mimmi20\Mezzio\Navigation\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageFactory;
use Mimmi20\Mezzio\Navigation\Page\PageFactoryInterface;
use Mimmi20\Mezzio\Navigation\Service\ConstructedNavigationFactory;
use Mimmi20\Mezzio\Navigation\Service\DefaultNavigationFactory;
use Mimmi20\NavigationHelper\Accept\AcceptHelperFactory;
use Mimmi20\NavigationHelper\Accept\AcceptHelperInterface;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserFactory;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParserInterface;
use Mimmi20\NavigationHelper\ConvertToPages\ConvertToPagesFactory;
use Mimmi20\NavigationHelper\ConvertToPages\ConvertToPagesInterface;
use Mimmi20\NavigationHelper\FindActive\FindActiveFactory;
use Mimmi20\NavigationHelper\FindActive\FindActiveInterface;
use Mimmi20\NavigationHelper\FindFromProperty\FindFromPropertyFactory;
use Mimmi20\NavigationHelper\FindFromProperty\FindFromPropertyInterface;
use Mimmi20\NavigationHelper\FindRoot\FindRoot;
use Mimmi20\NavigationHelper\FindRoot\FindRootInterface;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyFactory;
use Mimmi20\NavigationHelper\Htmlify\HtmlifyInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Stringable;
use Throwable;

use function method_exists;

/**
 * @group Compare
 * @group Laminas_View
 */
final class PluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     * @throws InvalidServiceException
     * @throws ContainerModificationsNotAllowedException
     */
    public function testInjectsParentContainerIntoHelpers(): void
    {
        $sm = new ServiceManager();
        $sm->setAllowOverride(true);

        $sm->setFactory('Navigation', DefaultNavigationFactory::class);
        $sm->setFactory('nav_test1', new ConstructedNavigationFactory('nav_test1'));
        $sm->setFactory('nav_test2', new ConstructedNavigationFactory('nav_test2'));
        $sm->setFactory('nav_test3', new ConstructedNavigationFactory('nav_test3'));
        $sm->setFactory(PageFactory::class, InvokableFactory::class);
        $sm->setAlias(PageFactoryInterface::class, PageFactory::class);
        $sm->setFactory(
            'config',
            static fn (): array => [
                'navigation' => [
                    'default' => [],
                ],
                'view_helpers' => [
                    'aliases' => [
                        'navigation' => \Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation::class,
                        'Navigation' => Navigation::class,
                        BaseServerUrlHelper::class => ServerUrlHelper::class,
                        'serverurl' => ServerUrlHelper::class,
                        'serverUrl' => ServerUrlHelper::class,
                        'ServerUrl' => ServerUrlHelper::class,
                        BaseUrlHelper::class => UrlHelper::class,
                        'url' => UrlHelper::class,
                        'Url' => UrlHelper::class,
                    ],
                    'factories' => [
                        Navigation::class => NavigationFactory::class,
                        UrlHelper::class => UrlHelperFactory::class,
                        ServerUrlHelper::class => ServerUrlHelperFactory::class,
                    ],
                ],
            ],
        );
        $sm->setFactory(ViewHelperPluginManager::class, HelperPluginManagerFactory::class);
        $sm->setFactory(PartialRendererInterface::class, PartialRendererFactory::class);
        $sm->setFactory(HtmlElementInterface::class, HtmlElementFactory::class);
        $sm->setFactory(HtmlifyInterface::class, HtmlifyFactory::class);
        $sm->setFactory(ContainerParserInterface::class, ContainerParserFactory::class);
        $sm->setAlias(FindRootInterface::class, FindRoot::class);
        $sm->setFactory(FindRoot::class, InvokableFactory::class);
        $sm->setFactory(AcceptHelperInterface::class, AcceptHelperFactory::class);
        $sm->setFactory(FindActiveInterface::class, FindActiveFactory::class);
        $sm->setFactory(FindFromPropertyInterface::class, FindFromPropertyFactory::class);
        $sm->setFactory(ConvertToPagesInterface::class, ConvertToPagesFactory::class);
        $sm->setFactory(LaminasViewRenderer::class, LaminasViewRendererFactory::class);
        $sm->setFactory(BaseServerUrlHelper::class, InvokableFactory::class);
        $sm->setFactory(
            LoggerInterface::class,
            /**
             * @throws void
             *
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            static fn (ContainerInterface $container, $requestedName, array | null $options = null) => new class () implements LoggerInterface {
                    /**
                     * System is unusable.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function emergency(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement emergency() method.
                    }

                    /**
                     * Action must be taken immediately.
                     *
                     * Example: Entire website down, database unavailable, etc. This should
                     * trigger the SMS alerts and wake you up.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function alert(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement alert() method.
                    }

                    /**
                     * Critical conditions.
                     *
                     * Example: Application component unavailable, unexpected exception.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function critical(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement critical() method.
                    }

                    /**
                     * Runtime errors that do not require immediate action but should typically
                     * be logged and monitored.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function error(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement error() method.
                    }

                    /**
                     * Exceptional occurrences that are not errors.
                     *
                     * Example: Use of deprecated APIs, poor use of an API, undesirable things
                     * that are not necessarily wrong.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function warning(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement warning() method.
                    }

                    /**
                     * Normal but significant events.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function notice(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement notice() method.
                    }

                    /**
                     * Interesting events.
                     *
                     * Example: User logs in, SQL logs.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function info(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement info() method.
                    }

                    /**
                     * Detailed debug information.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function debug(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement debug() method.
                    }

                    /**
                     * Logs with an arbitrary level.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function log(mixed $level, Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement log() method.
                    }
            },
        );
        $helpers = new PluginManager($sm);

        $helper = $helpers->get('breadcrumbs');
        self::assertInstanceOf(Breadcrumbs::class, $helper);
        self::assertSame($sm, $helper->getServiceLocator());
    }

    /** @throws ContainerModificationsNotAllowedException */
    public function testRegisteringInvalidElementRaisesException(): void
    {
        $this->expectException($this->getServiceNotFoundException());
        $this->expectExceptionCode(0);

        self::getPluginManager()->setService('test', $this);
    }

    /**
     * @throws ServiceNotFoundException
     * @throws InvalidServiceException
     * @throws ContainerModificationsNotAllowedException
     */
    public function testLoadingInvalidElementRaisesException(): void
    {
        $manager = self::getPluginManager();
        $manager->setFactory(
            'test',
            /**
             * @throws void
             *
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            static fn (ContainerInterface $container, $requestedName, array | null $options = null) => new self(
                'test',
            ),
        );
        $this->expectException($this->getServiceNotFoundException());
        $this->expectExceptionMessage(
            'Mezzio\Navigation\LaminasView\View\Helper\Navigation\PluginManager can only create instances of Laminas\View\Helper\HelperInterface and/or callables; Mimmi20Test\Mezzio\Navigation\LaminasView\Compare\PluginManagerCompatibilityTest is invalid',
        );
        $this->expectExceptionCode(0);

        $manager->get('test');
    }

    /**
     * @return PluginManager<HelperInterface>
     *
     * @throws ContainerModificationsNotAllowedException
     */
    protected static function getPluginManager(): PluginManager
    {
        $sm = new ServiceManager();
        $sm->setAllowOverride(true);

        $sm->setFactory('Navigation', DefaultNavigationFactory::class);
        $sm->setFactory('nav_test1', new ConstructedNavigationFactory('nav_test1'));
        $sm->setFactory('nav_test2', new ConstructedNavigationFactory('nav_test2'));
        $sm->setFactory('nav_test3', new ConstructedNavigationFactory('nav_test3'));
        $sm->setFactory(PageFactory::class, InvokableFactory::class);
        $sm->setAlias(PageFactoryInterface::class, PageFactory::class);
        $sm->setFactory(ViewHelperPluginManager::class, HelperPluginManagerFactory::class);
        $sm->setFactory(PartialRendererInterface::class, PartialRendererFactory::class);
        $sm->setFactory(HtmlElementInterface::class, HtmlElementFactory::class);
        $sm->setFactory(HtmlifyInterface::class, HtmlifyFactory::class);
        $sm->setFactory(ContainerParserInterface::class, ContainerParserFactory::class);
        $sm->setAlias(FindRootInterface::class, FindRoot::class);
        $sm->setFactory(FindRoot::class, InvokableFactory::class);
        $sm->setFactory(AcceptHelperInterface::class, AcceptHelperFactory::class);
        $sm->setFactory(FindActiveInterface::class, FindActiveFactory::class);
        $sm->setFactory(FindFromPropertyInterface::class, FindFromPropertyFactory::class);
        $sm->setFactory(ConvertToPagesInterface::class, ConvertToPagesFactory::class);
        $sm->setFactory(LaminasViewRenderer::class, LaminasViewRendererFactory::class);
        $sm->setFactory(BaseServerUrlHelper::class, InvokableFactory::class);
        $sm->setFactory(
            LoggerInterface::class,
            /**
             * @throws void
             *
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            static fn (ContainerInterface $container, $requestedName, array | null $options = null) => new class () implements LoggerInterface {
                    /**
                     * System is unusable.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function emergency(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement emergency() method.
                    }

                    /**
                     * Action must be taken immediately.
                     *
                     * Example: Entire website down, database unavailable, etc. This should
                     * trigger the SMS alerts and wake you up.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function alert(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement alert() method.
                    }

                    /**
                     * Critical conditions.
                     *
                     * Example: Application component unavailable, unexpected exception.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function critical(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement critical() method.
                    }

                    /**
                     * Runtime errors that do not require immediate action but should typically
                     * be logged and monitored.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function error(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement error() method.
                    }

                    /**
                     * Exceptional occurrences that are not errors.
                     *
                     * Example: Use of deprecated APIs, poor use of an API, undesirable things
                     * that are not necessarily wrong.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function warning(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement warning() method.
                    }

                    /**
                     * Normal but significant events.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function notice(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement notice() method.
                    }

                    /**
                     * Interesting events.
                     *
                     * Example: User logs in, SQL logs.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function info(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement info() method.
                    }

                    /**
                     * Detailed debug information.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function debug(Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement debug() method.
                    }

                    /**
                     * Logs with an arbitrary level.
                     *
                     * @param array<mixed> $context
                     *
                     * @throws void
                     *
                     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                     */
                    public function log(mixed $level, Stringable | string $message, array $context = []): void
                    {
                        // TODO: Implement log() method.
                    }
            },
        );
        $sm->setFactory(
            'config',
            static fn (): array => [
                'view_helpers' => [
                    'aliases' => [
                        'navigation' => \Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation::class,
                        'Navigation' => Navigation::class,
                        BaseServerUrlHelper::class => ServerUrlHelper::class,
                        'serverurl' => ServerUrlHelper::class,
                        'serverUrl' => ServerUrlHelper::class,
                        'ServerUrl' => ServerUrlHelper::class,
                        BaseUrlHelper::class => UrlHelper::class,
                        'url' => UrlHelper::class,
                        'Url' => UrlHelper::class,
                    ],
                    'factories' => [
                        Navigation::class => NavigationFactory::class,
                        UrlHelper::class => UrlHelperFactory::class,
                        ServerUrlHelper::class => ServerUrlHelperFactory::class,
                    ],
                ],
            ],
        );

        return new PluginManager($sm);
    }

    /**
     * @return class-string<Throwable>
     *
     * @throws ContainerModificationsNotAllowedException
     */
    protected function getServiceNotFoundException(): string
    {
        $manager = $this->getPluginManager();

        if (method_exists($manager, 'configure')) {
            return InvalidServiceException::class;
        }

        return $this->getV2InvalidPluginException();
    }

    /**
     * @return class-string<Throwable>
     *
     * @throws void
     */
    protected function getV2InvalidPluginException(): string
    {
        return InvalidHelperException::class;
    }

    /** @throws void */
    protected function getInstanceOf(): string
    {
        return ViewHelperInterface::class;
    }
}
