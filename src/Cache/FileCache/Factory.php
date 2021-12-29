<?php

declare(strict_types=1);

namespace BuzzingPixel\Container\Cache\FileCache;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function class_exists;
use function is_string;

/**
 * @codeCoverageIgnore
 */
class Factory
{
    private string $classString;
    /** @var mixed[] */
    private array $dependencies;

    /**
     * @param mixed[] $dependencies
     */
    public function __construct(
        string $classString,
        array $dependencies
    ) {
        $this->classString  = $classString;
        $this->dependencies = $dependencies;
    }

    /**
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function buildDependency(ContainerInterface $container)
    {
        $dependencies = [];

        foreach ($this->dependencies as $dep) {
            if (is_string($dep) && class_exists($dep)) {
                $dependencies[] = $container->get($dep);

                continue;
            }

            $dependencies[] = $dep;
        }

        return new $this->classString(...$dependencies);
    }
}
