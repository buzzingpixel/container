<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\Cache\FileCache;

use function file_exists;
use function file_put_contents;
use function serialize;

/**
 * @codeCoverageIgnore
 */
class CacheFileHandler
{
    private string $cacheDirPath;

    private string $cacheFilePath;

    public function __construct(string $cacheDirectoryPath)
    {
        $this->cacheDirPath = $cacheDirectoryPath;

        $this->cacheFilePath = $this->cacheDirPath . '/container-cache.php';
    }

    public function makeCacheFile(): void
    {
        if (file_exists($this->cacheFilePath)) {
            return;
        }

        file_put_contents(
            $this->cacheFilePath,
            "<?php\nreturn [];\n",
        );
    }

    /**
     * @return callable[]
     */
    public function getCacheArray(): array
    {
        return require $this->cacheFilePath;
    }

    /**
     * @param callable[] $bindings
     */
    public function writeBindingsToCacheFile(array $bindings): void
    {
        $begin = "<?php\nreturn unserialize('";

        file_put_contents(
            $this->cacheFilePath,
            $begin . serialize($bindings) . "');\n",
        );
    }
}
