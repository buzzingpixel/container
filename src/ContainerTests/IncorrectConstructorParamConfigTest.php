<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\ContainerTests;

use BuzzingPixel\Container\Container;
use PHPUnit\Framework\TestCase;
use Throwable;
use TypeError;

use function assert;

class IncorrectConstructorParamConfigTest extends TestCase
{
    public function test(): void
    {
        $exception = null;

        try {
            new Container(
                [],
                /** @phpstan-ignore-next-line */
                ['foo' => 'bar'],
            );
        } catch (Throwable $e) {
            $exception = $e;
        }

        assert($exception instanceof TypeError);

        self::assertSame(
            'Argument 1 passed to BuzzingPixel\Container\Container::BuzzingPixel\Container\{closure}() must be an instance of BuzzingPixel\Container\ConstructorParamConfig, string given',
            $exception->getMessage(),
        );
    }
}
