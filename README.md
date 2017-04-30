# The DI Package [![Build Status](https://travis-ci.org/joomla-framework/di.png?branch=master)](https://travis-ci.org/joomla-framework/di) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/joomla-framework/di/badges/quality-score.png?b=2.0-dev)](https://scrutinizer-ci.com/g/joomla-framework/di/?branch=2.0-dev) [![Code Coverage](https://scrutinizer-ci.com/g/joomla-framework/di/badges/coverage.png?b=2.0-dev)](https://scrutinizer-ci.com/g/joomla-framework/di/?branch=2.0-dev)

[![Latest Stable Version](https://poser.pugx.org/joomla/di/v/stable)](https://packagist.org/packages/joomla/di)
[![Total Downloads](https://poser.pugx.org/joomla/di/downloads)](https://packagist.org/packages/joomla/di)
[![Latest Unstable Version](https://poser.pugx.org/joomla/di/v/unstable)](https://packagist.org/packages/joomla/di)
[![License](https://poser.pugx.org/joomla/di/license)](https://packagist.org/packages/joomla/di)

The Joomla! **Dependency Injection** package provides a powerful `PSR-11` compatible
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

Please review [http://framework.joomla.org/contribute](http://framework.joomla.org/contribute) for information
on how to contribute to the Framework's development.
