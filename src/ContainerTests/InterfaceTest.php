<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\ContainerTests;

use BuzzingPixel\Container\Container;
use BuzzingPixel\Container\ContainerTestClasses\SomeContract;
use BuzzingPixel\Container\ContainerTestClasses\SomeImplementation;
use BuzzingPixel\Container\EntryNotFound;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Throwable;

use function assert;

class InterfaceTest extends TestCase
{
    public function testWhenImplementationHasNotBeenSpecified(): void
    {
        $container = new Container();

        self::assertFalse($container->has(SomeContract::class));

        $exception = null;

        try {
            $container->get(SomeContract::class);
        } catch (Throwable $e) {
            $exception = $e;
        }

        assert($exception instanceof EntryNotFound);

        self::assertSame(
            'An implementation has not been specified for ' .
            'the interface: ' . SomeContract::class,
            $exception->getMessage(),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testWhenImplementationHasBeenSpecified(): void
    {
        $container = new Container([
            SomeContract::class => SomeImplementation::class,
        ]);

        self::assertTrue($container->has(SomeContract::class));

        $someImplementation = $container->get(SomeContract::class);

        self::assertInstanceOf(
            SomeImplementation::class,
            $someImplementation,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testWhenImplementationIsSetViaFactory(): void
    {
        $container = new Container([
            SomeContract::class => static function (): SomeImplementation {
                return new SomeImplementation();
            },
        ]);

        self::assertTrue($container->has(SomeContract::class));

        $someImplementation = $container->get(SomeContract::class);

        self::assertInstanceOf(
            SomeImplementation::class,
            $someImplementation,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testWhenImplementationIsSetViaFactoryWithDi(): void
    {
        $container = new Container([
            SomeContract::class => static function (
                ContainerInterface $container
            ): SomeImplementation {
                /** @phpstan-ignore-next-line */
                return $container->get(SomeImplementation::class);
            },
        ]);

        self::assertTrue($container->has(SomeContract::class));

        $someImplementation = $container->get(SomeContract::class);

        self::assertInstanceOf(
            SomeImplementation::class,
            $someImplementation,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testWhenImplementationIsSetDirectly(): void
    {
        $container = new Container([
            SomeContract::class => new SomeImplementation(),
        ]);

        self::assertTrue($container->has(SomeContract::class));

        $someImplementation = $container->get(SomeContract::class);

        self::assertInstanceOf(
            SomeImplementation::class,
            $someImplementation,
        );
    }
}
