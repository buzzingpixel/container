<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\ContainerTests;

use BuzzingPixel\Container\Container;
use LogicException;
use PHPUnit\Framework\TestCase;
use Throwable;

use function assert;

class IncorrectConstructorBindingsTest extends TestCase
{
    public function test(): void
    {
        $exception = null;

        try {
            /** @phpstan-ignore-next-line */
            new Container([123 => 456]);
        } catch (Throwable $e) {
            $exception = $e;
        }

        assert($exception instanceof LogicException);

        self::assertSame(
            'Item bust be string, callable, or object',
            $exception->getMessage(),
        );
    }
}
