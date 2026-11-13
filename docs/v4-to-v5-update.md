# Updating from v4 to v5

Release 5.0.0 adds native parameter, return and property types to `Container` and
`ContainerResource`, and `buildObject()` throws instead of returning `false` for a class that does
not exist.

## At a glance

| | v4 | v5 (5.0.0) |
|---|---|---|
| PHP | `^8.3.0` | unchanged |
| `Container` / `ContainerResource` | docblock types | **native types** |
| `buildObject()` / `buildSharedObject()` for a missing class | returns `false` | **throws `DependencyResolutionException`** |
| `buildObject()` / `buildSharedObject()` return type | `object\|false` (docblock) | `object` |

## Native types on `Container`

All public and protected methods and properties of `Container` now declare their types:

```php
// v4
protected $aliases = [];
protected $resources = [];
protected $parent;
protected $tags = [];

public function get($resourceName)
public function has($resourceName): bool
public function alias($alias, $key)
protected function resolveAlias($resourceName)
public function tag($tag, array $keys)
public function getTagged($tag)
public function buildObject($resourceName, $shared = false)
public function buildSharedObject($resourceName)
public function createChild()
public function extend($resourceName, callable $callable)
public function set($key, $value, $shared = false, $protected = false)
public function protect($key, $value, $shared = false)
public function share($key, $value, $protected = false)
public function getNewInstance($key)
public function registerServiceProvider(ServiceProviderInterface $provider)
public function getKeys()

// v5
protected array $aliases = [];
protected array $resources = [];
protected ?ContainerInterface $parent = null;
protected array $tags = [];

public function get(string $resourceName): mixed
public function has(string $resourceName): bool
public function alias(string $alias, string $key): static
protected function resolveAlias(string $resourceName): string
public function tag(string $tag, array $keys): static
public function getTagged(string $tag): array
public function buildObject(string $resourceName, bool $shared = false): object
public function buildSharedObject(string $resourceName): object
public function createChild(): static
public function extend(string $resourceName, callable $callable): void
public function set(string $key, mixed $value, bool $shared = false, bool $protected = false): static
public function protect(string $key, mixed $value, bool $shared = false): static
public function share(string $key, mixed $value, bool $protected = false): static
public function getNewInstance(string $key): mixed
public function registerServiceProvider(ServiceProviderInterface $provider): static
public function getKeys(): array
```

Callers that pass strings as keys and booleans as flags are unaffected. Code that passes other
scalars (for example an integer key, or `0`/`1` as a flag) only keeps working without
`declare(strict_types=1)`, where PHP still coerces them.

A **subclass** of `Container` that overrides one of these methods must declare compatible types:
the same or wider parameter types and the same or a narrower return type. A subclass that
redeclares one of the properties must give it the same type. PHP refuses to load the class
otherwise.

`ContainerResource` is `@internal`, but its signatures changed the same way:

```php
// v4
public function __construct(Container $container, $value, int $mode = 0)
public function getInstance()

// v5
public function __construct(Container $container, mixed $value, int $mode = 0)
public function getInstance(): mixed
```

`ContainerAwareInterface`, `ContainerAwareTrait` and `ServiceProviderInterface` are unchanged.

## `buildObject()` throws for a class that does not exist

In v4, `buildObject()` and `buildSharedObject()` returned `false` when the class did not exist,
while every other failure threw a `DependencyResolutionException`. They now throw for a missing
class as well, and declare `object` as return type. For the same reason, they also throw when the
key is already registered, but its resource is not an object.

```php
// v4
$object = $container->buildObject($className);

if ($object === false) {
    // the class does not exist
}

// v5
try {
    $object = $container->buildObject($className);
} catch (DependencyResolutionException $e) {
    // the class does not exist, or it could not be built
}
```

To find the call sites that check for `false`:

```bash
grep -rn -E 'build(Shared)?Object\(' src/
```

## Dependency changes

None.
