<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\Cache\FileCache;

use BuzzingPixel\Container\Cache\CacheAdapterContract;
use Throwable;

use function get_class;

/**
 * @codeCoverageIgnore
 */
class FileCache implements CacheAdapterContract
{
    private CacheFileHandler $cacheFileHandler;

    public function __construct(CacheFileHandler $cacheFileHandler)
    {
        $this->cacheFileHandler = $cacheFileHandler;

        $cacheFileHandler->makeCacheFile();
    }

    /**
     * @param mixed[] $constructorDependencyValues
     */
    public function cacheInstanceBinding(
        string $classString,
        array $constructorDependencyValues
    ): void {
        $dependencies = [];

        foreach ($constructorDependencyValues as $value) {
            try {
                /** @phpstan-ignore-next-line */
                $val = get_class($value);
            } catch (Throwable $exception) {
                $val = $value;
            }

            $dependencies[] = $val;
        }

        $existingBindings = $this->getCachedBindings();

        $existingBindings[$classString] = [
            new Factory(
                $classString,
                $dependencies
            ),
            'buildDependency',
        ];

        $this->cacheFileHandler->writeBindingsToCacheFile(
            $existingBindings,
        );
    }

    /**
     * @return callable[]
     */
    public function getCachedBindings(): array
    {
        return $this->cacheFileHandler->getCacheArray();
    }
}
