<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\Cache;

class NoOpCacheAdapter implements CacheAdapterContract
{
    /**
     * @inheritDoc
     */
    public function cacheInstanceBinding(
        string $classString,
        array $constructorDependencyValues
    ): void {
    }

    /**
     * @inheritDoc
     */
    public function getCachedBindings(): array
    {
        return [];
    }
}
