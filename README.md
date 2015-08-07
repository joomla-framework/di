# The DI Package [![Build Status](https://travis-ci.org/joomla-framework/di.png?branch=master)](https://travis-ci.org/joomla-framework/di)

The Joomla! **Dependency Injection** package provides a powerful `container-interop` (upcoming `PSR-11`) compatible
Inversion of Control (IoC) Container for your application.

## Installation via Composer

Simply run the following from the command line in your project's root directory (where your `composer.json` file is):

```sh
composer require joomla/di "2.0.*@dev"
```

Alternatively, you can manually add `"joomla/di": "2.0.*@dev"` to the require block in your `composer.json`
and then run `composer install`.

```json
{
	"require": {
		"joomla/di": "2.0.*@dev"
	}
}
```

## Upgrade from 1.x to 2.0

  - The `exists` method was renamed to `has`. Change the method name.
  - The second (optional) argument on `get` to enforce recreation on shared resources was removed. Use `getNewInstance` instead.
  
## Documentation

  1. [Overview](docs/overview.md)
  2. [Why Dependency Injection](docs/why-dependency-injection.md)
  
## Contributing

