<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\ContainerTestClasses;

class ComplexAutoWire
{
    private SimpleAutoWire $simpleAutoWire;

    private SomeContract $someContract;

    private string $apiKey;

    public function __construct(
        SimpleAutoWire $simpleAutoWire,
        SomeContract $someContract,
        string $apiKey
    ) {
        $this->simpleAutoWire = $simpleAutoWire;
        $this->someContract   = $someContract;
        $this->apiKey         = $apiKey;
    }

    public function simpleAutoWire(): SimpleAutoWire
    {
        return $this->simpleAutoWire;
    }

    public function someContract(): SomeContract
    {
        return $this->someContract;
    }

    public function apiKey(): string
    {
        return $this->apiKey;
    }
}
