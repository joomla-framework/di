# Updating from v3 to v4

Release 4.0.0 raises the PHP requirement, removes the long-deprecated `exists()` method and moves
to `psr/container` 2.0.

## At a glance

| | v3 (3.1.0) | v4 (4.0.0) |
|---|---|---|
| PHP | `^8.1.0` | `^8.3.0` |
| `Container::exists()` | deprecated, works | **removed** |
| `Container::has()` | untyped return | `: bool` |
| `psr/container` | `^1.0` | `^2.0` |

## Minimum supported PHP version raised

All Framework packages now require **PHP 8.3** or newer.

## `Container::exists()` was removed

Deprecated since 3.0 in favour of the PSR-11 method:

```php
// Removed in 4.0.0
if ($container->exists(LoggerInterface::class)) { … }

// Use
if ($container->has(LoggerInterface::class)) { … }
```

To find the call sites:

```bash
grep -rn -- '->exists(' src/
```

## `Container::has()` declares its return type

```php
// v3
public function has($resourceName)

// v4
public function has($resourceName): bool
```

Callers are unaffected. A subclass overriding `has()` must add the return type, otherwise PHP
refuses to load the class.

## `psr/container` 2.0

PSR-11 2.0 adds parameter and return types to the interface:

```php
// psr/container 1.x
public function get($id);
public function has($id);

// psr/container 2.0
public function get(string $id): mixed;
public function has(string $id): bool;
```

`Container` satisfies both. This matters if you **implement** `ContainerInterface` yourself — for
example a small container handed to `Joomla\Application\Controller\ContainerControllerResolver`, or
a parent container passed to `Container::__construct()`. Add the types to match.

Note that `Container::get()` still declares `get($resourceName)` without the `string` parameter type
and without a return type. That is allowed — a parameter type may be omitted where the interface
declares one — so no change is needed on your side.

## Dependency changes

| Package | v3 (3.1.0) | v4 (4.0.0) |
|---|---|---|
| `php` | `^8.1.0` | `^8.3.0` |
| `psr/container` | `^1.0` | `^2.0` |
| `symfony/deprecation-contracts` | `^2 \| ^3` | unchanged |
