<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\ContainerTestClasses;

class AutoWire2
{
    /**
     * @codingStandardsIgnoreStart
     * @phpstan-ignore-next-line
     */
    private $fooBar;

    /**
     * @codingStandardsIgnoreStart
     * @phpstan-ignore-next-line
     */
    public function __construct($fooBar)
    {
        $this->fooBar = $fooBar;
    }

    /**
     * @codingStandardsIgnoreStart
     * @phpstan-ignore-next-line
     */
    public function fooBar()
    {
        return $this->fooBar;
    }
}
