<?php

declare(strict_types=1);

namespace BuzzingPixel\Container;

use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use RuntimeException;
use Throwable;

use function array_filter;
use function array_map;
use function array_values;
use function array_walk;
use function class_exists;
use function gettype;
use function in_array;
use function interface_exists;
use function is_callable;
use function is_string;

class Container implements ContainerInterface
{
    /**
     * This stores our bindings. The key is the ID string.
     * If value is string, it is an ID to something else in the container. If it
     * is callable, it is a factory. If it's an object, it is the item.
     *
     * @var array<string, string|callable|object>
     */
    private array $bindings = [];

    /**
     * Constructor param configurations for auto-wiring
     *
     * @var ConstructorParamConfig[]
     */
    private array $constructorParamConfigs = [];

    /**
     * When an item has been constructed during the course of the request, it
     * is cached here
     *
     * @var array<string, mixed>
     */
    private array $runTimeCache = [];

    /**
     * Provide any bindings. The key is the ID of the binding (usually a
     * class name). The value has three possible types:
     *     string: This is an ID to another item in the container. Usually a
     *     class name. Use this most often to bind an interface to an
     *     implementation.
     *     callable: Set a callable that returns the appropriate item for the
     *     id. The callback will receive this container as an argument.
     *     object: Set a concrete object to be returned for the ID
     *
     * Provide any ConstructorParamConfig s for auto-wiring
     *
     * @param array<string, string|callable|object> $bindings
     * @param ConstructorParamConfig[]              $constructorParamConfigs
     */
    public function __construct(
        array $bindings = [],
        array $constructorParamConfigs = []
    ) {
        // Bind this container implementation to its class name
        $bindings[self::class] = $this;

        // Bind the container interface to this class name
        $bindings[ContainerInterface::class] = self::class;

        // Walk through our bindings to make sure they're what we expect
        array_walk(
            $bindings,
            function ($objectOrId, string $id): void {
                $type = gettype($objectOrId);

                if (
                    ! is_callable($objectOrId) &&
                    ! in_array(
                        $type,
                        ['string', 'object'],
                        true,
                    )
                ) {
                    throw new LogicException(
                        'Item bust be string, callable, or object',
                    );
                }

                $this->bindings[$id] = $objectOrId;
            }
        );

        // Walk through constructor param configs to make sure they're what
        // we expect
        array_walk(
            $constructorParamConfigs,
            function (ConstructorParamConfig $config): void {
                $this->constructorParamConfigs[] = $config;
            }
        );
    }

    /**
     * @throws ReflectionException
     *
     * @inheritDoc
     */
    public function get(string $id)
    {
        if (! isset($this->runTimeCache[$id])) {
            $this->runTimeCache[$id] = $this->getDefinition($id);
        }

        return $this->runTimeCache[$id];
    }

    public function has(string $id): bool
    {
        try {
            $this->get($id);

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Get a definition from an ID
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function getDefinition(string $id)
    {
        // Check if we have a binding to use
        $binding = $this->bindings[$id] ?? null;

        // If there's no binding, we should try to auto-wire it
        if ($binding === null) {
            return $this->autoWire($id);
        }

        // If the binding is a string, it's a pointer to another definition
        if (is_string($binding)) {
            return $this->get($binding);
        }

        // If the binding is callable, we should treat it like a factory
        if (is_callable($binding)) {
            return $binding($this);
        }

        // Otherwise, we shall assume the binding is our definition
        return $binding;
    }

    /**
     * We're going to attempt to auto-wire the ID
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function autoWire(string $id): object
    {
        // If the class does not exist, then we can't auto-wire it
        if (! class_exists($id)) {
            if (interface_exists($id)) {
                throw new EntryNotFound(
                    'An implementation has not been specified for ' .
                    'the interface: ' . $id,
                );
            }

            throw new EntryNotFound(
                'Cannot auto-wire ' . $id . '. Class does not exist',
            );
        }

        // Let's reflect upon our class
        $ref = new ReflectionClass($id);

        // Get an array of the dependencies from the reflection
        $dependencies = $this->buildDependencies($ref, $id);

        // Create a new instance from those dependencies
        return $ref->newInstanceArgs($dependencies);
    }

    /**
     * Build the dependencies of a reflected class
     *
     * @return mixed[]
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     *
     * @phpstan-ignore-next-line
     */
    private function buildDependencies(ReflectionClass $ref, string $id): array
    {
        // First we'll get the constructor
        $constructor = $ref->getConstructor();

        // If there's no constructor, there's nothing here to do. We're good
        // to go.
        if ($constructor === null) {
            return [];
        }

        // Get each of the params from the constructor
        $params = $constructor->getParameters();

        // Iterate over each param, infer its type, and get it
        return array_map(
            function (ReflectionParameter $param) use ($id) {
                return $this->buildDependencyFromParam($param, $id);
            },
            $params,
        );
    }

    /**
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function buildDependencyFromParam(
        ReflectionParameter $param,
        string $id
    ) {
        // Get the param's type
        $type = $param->getType();

        // Check if there's a param config for this param
        $paramConfig = array_values(array_filter(
            $this->constructorParamConfigs,
            static function (
                ConstructorParamConfig $config
            ) use (
                $param,
                $id
            ): bool {
                    return $id === $config->id() &&
                        $param->getName() === $config->param();
            }
        ))[0] ?? null;

        // If there is, build a dependency from that
        if ($paramConfig !== null) {
            return $this->buildDependencyFromParamConfig(
                $paramConfig
            );
        }

        // If there is no type, we have to bail out
        if ($type === null) {
            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            throw new RuntimeException(
                'Cannot infer type of param ' . $param->getName() .
                    ' for ' . $id,
            );
        }

        /**
         * If the container has the type, use that
         *
         * @phpstan-ignore-next-line
         */
        if ($this->has($type->getName())) {
            /** @phpstan-ignore-next-line */
            return $this->get($type->getName());
        }

        // We can't get a type, but if we have a default value, do that
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        // We can't get a type, but if it allows null, we can do that
        if ($type->allowsNull()) {
            return null;
        }

        // And finally, it has come to this. We can't build the dependency
        throw new RuntimeException(
            /** @phpstan-ignore-next-line */
            'Could not build dependency ' . $type->getName() .
                ' for ' . $id
        );
    }

    /**
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function buildDependencyFromParamConfig(
        ConstructorParamConfig $paramConfig
    ) {
        $give = $paramConfig->give();

        // If the container has the give value, get that
        if (is_string($give) && $this->has($give)) {
            return $this->get($give);
        }

        // Otherwise, just return the value
        return $paramConfig->give();
    }
}
