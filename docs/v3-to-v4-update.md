## Updating from v3 to v4

The following changes were made to the DI package between v3 and v4.

### Minimum supported PHP version raised

All Framework packages now require PHP 8.3 or newer.

### `Container::exists()` was removed

The method `Container::exists()` was removed. Use `Container::has()` instead.
