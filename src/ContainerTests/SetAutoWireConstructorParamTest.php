<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\ContainerTests;

use BuzzingPixel\Container\ConstructorParamConfig;
use BuzzingPixel\Container\Container;
use BuzzingPixel\Container\ContainerTestClasses\AutoWire2;
use BuzzingPixel\Container\ContainerTestClasses\ComplexAutoWire;
use BuzzingPixel\Container\ContainerTestClasses\SimpleAutoWire;
use BuzzingPixel\Container\ContainerTestClasses\SomeContract;
use BuzzingPixel\Container\ContainerTestClasses\SomeImplementation;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use RuntimeException;
use Throwable;

use function assert;

class SetAutoWireConstructorParamTest extends TestCase
{
    public function testWhenParamsNotSet(): void
    {
        $container = new Container(
            ['foo' => 'bar'],
            [
                new ConstructorParamConfig(
                    'fooBar',
                    'apiKey',
                    'fooBarApiKey',
                ),
            ],
        );

        self::assertFalse(
            $container->has(ComplexAutoWire::class)
        );

        $exception = null;

        try {
            $container->get(ComplexAutoWire::class);
        } catch (Throwable $e) {
            $exception = $e;
        }

        assert($exception instanceof RuntimeException);

        self::assertSame(
            'Could not build dependency ' . SomeContract::class .
                ' for ' . ComplexAutoWire::class,
            $exception->getMessage(),
        );
    }

    public function testWhenStringParamIsNotSet(): void
    {
        $container = new Container(
            [
                'foo' => 'bar',
                SomeContract::class => SomeImplementation::class,
            ],
            [
                new ConstructorParamConfig(
                    'fooBar',
                    'apiKey',
                    'fooBarApiKey',
                ),
            ],
        );

        self::assertFalse(
            $container->has(ComplexAutoWire::class)
        );

        $exception = null;

        try {
            $container->get(ComplexAutoWire::class);
        } catch (Throwable $e) {
            $exception = $e;
        }

        assert($exception instanceof RuntimeException);

        self::assertSame(
            'Could not build dependency string for ' .
                ComplexAutoWire::class,
            $exception->getMessage(),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testWhenParamsAreSet(): void
    {
        $container = new Container(
            [
                'foo' => 'bar',
                SomeContract::class => SomeImplementation::class,
            ],
            [
                new ConstructorParamConfig(
                    'fooBar',
                    'apiKey',
                    'asdfApiKey',
                ),
                new ConstructorParamConfig(
                    ComplexAutoWire::class,
                    'apiKey',
                    'fooBarApiKey',
                ),
            ],
        );

        self::assertTrue(
            $container->has(ComplexAutoWire::class),
        );

        $complexAutoWire = $container->get(ComplexAutoWire::class);

        assert($complexAutoWire instanceof ComplexAutoWire);

        self::assertInstanceOf(
            ComplexAutoWire::class,
            $complexAutoWire,
        );

        self::assertSame(
            $container->get(SimpleAutoWire::class),
            $complexAutoWire->simpleAutoWire(),
        );

        self::assertSame(
            $container->get(SomeImplementation::class),
            $complexAutoWire->someContract(),
        );

        self::assertSame(
            'fooBarApiKey',
            $complexAutoWire->apiKey(),
        );
    }

    public function testManualAutoWireWhenNotSet(): void
    {
        $container = new Container(
            ['foo' => 'bar'],
            [
                new ConstructorParamConfig(
                    'fooBar',
                    'apiKey',
                    'asdfApiKey',
                ),
            ],
        );

        self::assertFalse($container->has(AutoWire2::class));

        $exception = null;

        try {
            $container->get(AutoWire2::class);
        } catch (Throwable $e) {
            $exception = $e;
        }

        assert($exception instanceof RuntimeException);

        self::assertSame(
            'Cannot infer type of param fooBar for ' .
                AutoWire2::class,
            $exception->getMessage(),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testManualAutoWireWhenSet(): void
    {
        $container = new Container(
            ['foo' => 'bar'],
            [
                new ConstructorParamConfig(
                    'fooBar',
                    'apiKey',
                    'asdfApiKey',
                ),
                new ConstructorParamConfig(
                    AutoWire2::class,
                    'fooBar',
                    SimpleAutoWire::class,
                ),
            ],
        );

        self::assertTrue($container->has(AutoWire2::class));

        $autoWire2 = $container->get(AutoWire2::class);

        assert($autoWire2 instanceof AutoWire2);

        self::assertInstanceOf(
            AutoWire2::class,
            $autoWire2
        );

        self::assertSame(
            $container->get(SimpleAutoWire::class),
            $autoWire2->fooBar()
        );
    }
}
