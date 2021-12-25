<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\ContainerTests;

use BuzzingPixel\Container\Container;
use BuzzingPixel\Container\ContainerTestClasses\SimpleAutoWire;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class SimpleAutoWireTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function test(): void
    {
        $container = new Container();

        self::assertTrue($container->has(SimpleAutoWire::class));

        $simpleAutoWire1 = $container->get(SimpleAutoWire::class);

        $simpleAutoWire2 = $container->get(SimpleAutoWire::class);

        self::assertInstanceOf(
            SimpleAutoWire::class,
            $simpleAutoWire1
        );

        self::assertSame($simpleAutoWire1, $simpleAutoWire2);
    }
}
