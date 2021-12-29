# BuzzingPixel ContainerInterface implementation

A simple, concrete, easy to configure implementation of the PSR ContainerInterface. This container will auto-wire whatever it can. You can also provide bindings directly.

## Configuring bindings

Bindings are configured by an array in the Container's constructor.

The keys in this array should be the classnames you wish to provide bindings for.

The values can be:

### An object

If an object is provided, it will be treated as the concrete implementation for the classname.

### A string

If a string is provided as the value, it will be treated as an ID for another entry in the container and will recurse back to the `get` method to get the specified item. This is most useful for binding interfaces to concrete implementations.

### A callable

If a callable is provided, it will be called the first time the entry is requested and is expected to provide the concrete implementation. It receives as its only argument the container instance.

Example:

```php
$container = new \BuzzingPixel\Container\Container(
    bindings: [
        // You'd probably never do this exactly like this, but you get the idea
        SomeClass::class => new SomeClass(),

        // Bind a concrete implementation to an interface
        SomeInterface::class => SomeImplementation::class,

        // A factory method to create the requested class
        AnotherClass::class => function (\Psr\Container\ContainerInterface $container): AnotherClass {
            return new AnotherClass(
                apiKey: env('API_KEY'),
                someDependency: $container->get(SomeDependency::class),
            );
        }
    ],
);
```

## Configuring constructor params

Sometimes, you want to let auto-wiring do its thing, but you need to configure just one parameter on some class — say, maybe an API key string. That's why the container constructor has `array $constructorParamConfigs` as an argument. The array should be instances of `\BuzzingPixel\Container\ConstructorParamConfig`.

Through the `ConstructorParamConfig` constructor, you provide the name of the class to be configured (`$id`), the name of the param to configure, and the value to give.

Example:

```php
$container = new \BuzzingPixel\Container\Container(
    constructorParamConfigs: [
        new \BuzzingPixel\Container\ConstructorParamConfig(
            id: SomeClassToConfigure::class,
            param: 'apiKey', // or whatever the name of the param is
            give: 'fooBarApiKey',
        ),
        new \BuzzingPixel\Container\ConstructorParamConfig(
            id: AnotherClassToConfigure::class,
            param: 'someInterface',
            give: SomeConcreteImplementation::class,
        ),
    ],
);
```

## Caching

The container is pretty efficient, and for many applications, you may not need caching. However, if you're looking to eek out a little more performance in production, you can provide a cache implementation as the third argument in the constructor of the container. You _could_ write your own, or you can use the file cache implementation bundled with this package.

The cache is invoked for any auto-wired class so that after it's been auto-wired, a factory is written to the cache file and loaded into the bindings of the container on next request so that reflection is not used for this class on future requests.

Example:

```php
$container = new \BuzzingPixel\Container\Container(
    cacheAdapter: new \BuzzingPixel\Container\Cache\FileCache\FileCache(
        cacheFileHandler: new \BuzzingPixel\Container\Cache\FileCache\CacheFileHandler(
            cacheDirectoryPath: '/path/to/desired/cache/directory',
        ),
    ),
);
```
