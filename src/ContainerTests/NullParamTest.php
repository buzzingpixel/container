<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\ContainerTests;

use BuzzingPixel\Container\Container;
use BuzzingPixel\Container\ContainerTestClasses\ConstructorParamAllowsNull;
use PHPUnit\Framework\TestCase;

use function assert;

class NullParamTest extends TestCase
{
    public function testWhenNullAllowedParamNotSet(): void
    {
        $container = new Container();

        self::assertTrue($container->has(
            ConstructorParamAllowsNull::class
        ));

        $object = $container->get(ConstructorParamAllowsNull::class);

        assert($object instanceof ConstructorParamAllowsNull);

        self::assertNull($object->testApiKey());

        self::assertSame(
            'fooBarDefault',
            $object->fooBar(),
        );

        self::assertSame(
            'barBazDefault',
            $object->barBaz(),
        );
    }
}
