<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\Cache;

interface CacheAdapterContract
{
    /**
     * @param mixed[] $constructorDependencyValues
     */
    public function cacheInstanceBinding(
        string $classString,
        array $constructorDependencyValues
    ): void;

    /**
     * @return callable[]
     */
    public function getCachedBindings(): array;
}
