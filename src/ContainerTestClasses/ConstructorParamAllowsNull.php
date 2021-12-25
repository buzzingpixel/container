<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\ContainerTestClasses;

class ConstructorParamAllowsNull
{
    private ?string $testApiKey;

    private string $fooBar;

    private ?string $barBaz;

    /**
     * @codingStandardsIgnoreStart
     * @phpstan-ignore-next-line
     */
    public function __construct(
        ?string $testApiKey,
        string $fooBar = 'fooBarDefault',
        $barBaz = 'barBazDefault'
    ) {
        $this->testApiKey = $testApiKey;
        $this->fooBar     = $fooBar;
        $this->barBaz     = $barBaz;
    }

    public function testApiKey(): ?string
    {
        return $this->testApiKey;
    }

    public function fooBar(): string
    {
        return $this->fooBar;
    }

    public function barBaz(): ?string
    {
        return $this->barBaz;
    }
}
