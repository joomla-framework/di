## Overview

<!--
The source of this file contains inline HTML comments starting with '[x]'.
These are the lines from the test results in testdox format, which are verifying the statement made
in this documentation.

Use this as an example on how to transform test documentation into user documentation.
Of course that requires a good testbase.

To reproduce the test results, run `phpunit --testdox`
-->
The Joomla! Dependency Injection package provides a simple `container-interop` (upcoming `PSR-11`) compatible
Inversion of Control (IoC) Container for your application.

The Joomla! Dependency Injection Container supports

  - **factories** and **instances** (i.e., **closures**, **callables**, **objects**, **arrays**, and even **scalars**)
  - **shared** and **protected** modes
  - **caching** and **cloning** of resources according to the modes
  - enforcing **recreation** of resources
  - **aliases**
  - **scopes** using the **decorator** pattern for containers
  - **delegate lookup**
  - **object creation** on the fly to resolve dependencies
  - ability to **extend** resources
  - **service providers**

In this document,

  - **factory** is a callable or closure, that takes the container as argument and returns the resource instance;
  - **instance** is the resource instance, i.e., the return value of a factory, or an explicitly defined value (may be a scalar as well);
  - **resource** is a key/value pair, with the key being the id, and the value being a factory or an instance.

### Container Interoperability
    
The Joomla! Dependency Injection package implements the [PSR-11 proposal](https://github.com/container-interop/fig-standards/blob/master/proposed/container.md)
for Dependency Injection Containers to achieve interoperability.
Until PSR-11 gets accepted, Joomla! DI uses the [`container-interop`](https://github.com/container-interop/container-interop)
namespace.

### Creating a Container object

Creating a container usually happens very early in the application lifecycle. For a Joomla! MVC app, this
typically happens in the application's `doExecute` method. This allows your application access to the DI
Container, which you can then use within the app class to build your controllers and their dependencies.

```php
namespace My\App;

use Joomla\DI\Container;
use Joomla\Application\AbstractWebApplication;

class WebApp extends AbstractWebApplication
{
    protected $container;

    // ...snip

    protected function doExecute()
    {
        $this->container = new Container;

        // ...snip
    }
}
```

### Hierachical Containers

#### Decorating other Containers

<!-- [x] Container can decorate an arbitrary Interop compatible container -->
If you have any other container implementing the `ContainerInterface`, you can pass it to the constructor.

```php
use \Joomla\DI\Container;

$container = new Container($arbitraryInteropContainer);
```

<!-- [x] Container can manage an alias for a resource from an arbitrary Interop compatible container -->
You'll then be able to access any resource from `$arbitraryInteropContainer` through `$container`, thus virtually adding 
the features (like aliasing) of the Joomla! DI Container to the other one.

#### Scopes

The decoration feature can be used to manage different resolution scopes.

```php
use Joomla\DI\Container;

$container = new Container;

$container->set('Some\Interface\I\NeedInterface', new My\App\InterfaceImplementation);

// Application executes... Come to a class that needs a different implementation.

$child = new Container($container);
$child->set('Some\Interface\I\NeedInterface', new My\Other\InterfaceImplementation);
```

This allows you to easily override an interface binding for a specific controller, without
destroying the resolution scope for the rest of the classes using the container.
<!-- [x] Child container has access to parent's resources -->
<!-- [x] Child container resolves parent's alias to parent's resource -->
A child container will search recursively through its parent containers to resolve all the required dependencies.
For this behaviour, a convenience method `createChild` is provided.

```php
use Joomla\DI\Container;

$container = new Container;

$container->set('Some\Interface\I\NeedInterface', new My\App\InterfaceImplementation);

// Application executes... Come to a class that needs a different implementation.

$child = $container
    ->createChild()
    ->set('Some\Interface\I\NeedInterface', new My\Other\InterfaceImplementation);
```

### Setting an Item

Setting an item within the container is very straightforward.
<!-- [x] Resources can be set up with Callables -->
<!-- [x] Resources can be set up with Closures -->
<!-- [x] Resources can be scalar values -->
You pass the `set` method a string `$key`
and a `$value`, which can be pretty much anything. If the `$value` is an anonymous function or a `Closure`,
or a callable value, that value will be set as the resolving callback for the `$key`.
If it is anything else (an instantiated object, array, integer, serialized controller, etc) it will be wrapped
in a closure and that closure will be set as the resolving callback for the `$key`.

<!-- [x] The callback gets the container instance as a parameter -->
> If the `$value` you are setting is a closure or a callable, it will receive a single function argument,
> the calling container. This allows access to the container within your resolving callback.

```php
$container = new Container;

// Setting a scalar
$container->set('foo', 'bar');

// Setting an object
$container->set('something', new Something);

// Setting a callable
$container->set('callMe', array($this, 'callMe'));

// Setting a closure
$container->set(
    'baz',
    function (Container $c)
    {
        // some expensive $stuff;

        return $stuff;
    }
);
```

#### Resource Options

<!-- [x] 'shared' and 'protected' mode can be set independently -->
When setting items in the container, you can specify whether or not the item is supposed to be a
shared or protected item.

```php
$container = new Container;

$shared = true;
$protected = true;

$container->set(
    'foo',
    function ()
    {
        // some expensive $stuff;

        return $stuff;
    },
    $shared,
    $protected
);
```

<!-- [x] Default mode is 'not shared' and 'not protected' -->
Default mode is 'not shared' and 'not protected'.

<!-- [x] Resources from an arbitrary Interop compatible container are 'shared' and 'protected' -->
If a container was passed to the constructor, which is not a `Joomla\DI\Container`, the resources
from that container are treated as 'shared' and 'protected'.

`Container` provides convenience methods for setting shared and protected resources.

##### Shared Resources

A shared item means that when you get an item from the container, the resolving
callback will be fired once, and the value will be stored and used on every subsequent request for that
item. You can set a shared resource using the `share` method.

```php
$container = new Container;

$container->share(
    'foo',
    function ()
    {
        // some expensive $stuff;

        return $stuff;
    }
);
```

<!-- [x] The convenience method share() sets resources as shared, but not as protected by default -->
Resources set with `share` are not protected by default.
<!-- [x] The convenience method share() sets resources as protected when passed true as third arg -->
If you pass `true` as third argument, you can both share AND protect an item. A good use case for this would
be a database connection that you only want one of, and you don't want it to be overwritten.

You can check whether a resource is shared with the `isShared` method.

```php
var_dump($container->isShared('foo'); // prints bool(true) for the example above
```

##### Protected Resources

The other option, protected, is a special status that you can use to prevent others from overwriting
the item down the line. A good example for this would be a global config that you don't want to be
overwritten.
<!-- [x] Setting an existing protected resource throws an OutOfBoundsException -->
<!-- [x] Setting an existing non-protected resource replaces the resource -->

```php
$container = new Container;

// Don't overwrite my db connection.
$container->protect(
    'bar',
    function (Container $c)
    {
        $config = $c->get('config');

        $databaseConfig = (array) $config->get('database');

        return new DatabaseDriver($databaseConfig);
    }
);
```

<!-- [x] The convenience method protect() sets resources as protected, but not as shared by default -->
Resources set with `protect` are not shared by default.
<!-- [x] The convenience method protect() sets resources as shared when passed true as third arg -->
If you pass `true` as third argument, you can both share AND protect an item.

You can check whether a resource is protected with the `isProtected` method.

```php
var_dump($container->isProtected('bar'); // prints bool(true) for the example above
```

### Item Aliases

Any item set in the container can be aliased. This allows you to create an object that is a named
dependency for object resolution, but also have a "shortcut" access to the item from the container.
<!-- [x] Both the original key and the alias return the same resource -->
You get the same resource with the alias as you would with the original key.

```php
$container = new Container;

$container->set(
    'Really\Long\ConfigClassName',
    function ()
    {
        // ...snip
    }
);

$container->alias('config', 'Really\Long\ConfigClassName');

$container->get('config'); // Returns the value set on the aliased key.
```

### Getting an Item

At its most basic level, the DI Container is a registry that holds keys and values. When you set
an item on the container, you can retrieve it by passing the same `$key` to the `get` method that
you did when you set the method in the container.

> If you've aliased a set item, you can also retrieve it using the alias key.

```php
$container = new Container;

$container->set('foo', 'bar');

$foo = $container->get('foo'); // $foo now contains 'bar'
```

Normally, the value you'll be passing will be a closure. When you fetch the item from the container,
the closure is executed, and the result is returned.

```php
$container = new Container;

$container->set(
    'github',
    function ()
    {
        // Create an instance of \Joomla\Github\Github;

        return $github;
    }
);

$github = $container->get('github');

var_dump($github instanceof \Joomla\Github\Github); // prints bool(true)
```

<!-- [x] A new resource instance is returned for non-shared resources -->
If you get the item again, the closure is executed again and the result is returned.

```php
// Picking up from the previous codeblock

$github2 = $container->get('github');

var_dump($github2 === $github); // prints bool(false)
```

<!-- [x] The same resource instance is returned for shared resources -->
However, if you specify that the object as shared when setting it in the container, the closure will
only be executed once (the first time it is requested). The value will be stored and then returned
on every subsequent request.

```php
$container = new Container;

$container->share(
    'twitter',
    function ()
    {
        // Create an instance of \Joomla\Twitter\Twitter;

        return $twitter;
    }
);

$twitter  = $container->get('twitter');
$twitter2 = $container->get('twitter');

var_dump($twitter === $twitter2); // prints bool(true)
```

<!-- [x] getNewInstance() will always return a new instance, even if the resource was set to be shared -->
If you've specified an item as shared, but you really need a new instance of it for some reason, you
can force the creation of a new instance by using the `getNewInstance` method.

> When you force create a new instance on a shared object, that new instance replaces the instance
> that is stored in the container and will be used on subsequent requests.

```php
// Picking up from the previous codeblock

$twitter3 = $container->getNewInstance('twitter');

var_dump($twitter === $twitter3); // prints bool(false)

$twitter4 = $container->get('twitter');
var_dump($twitter3 === $twitter4); // prints bool(true)
```

<!-- [x] Accessing an undefined resource throws an InvalidArgumentException -->
If you try to retrieve a resource for an undefined key, an `InvalidArgumentException` is thrown.
<!-- [x] The existence of a resource can be checked -->
To avoid that, the existence of a resource can be checked with the `has` method.
It returns `true`, if the container knows the key, and `false` otherwise.
<!-- [x] has() also resolves the alias if set. -->
Of course, `has` also resolves an alias (if set).

### Building Objects

The most useful function of the container is its ability to build complete objects, instantiating
any needed dependency along the way. When you use the container in this way, it looks at a class'
constructor declared dependencies and then automatically passes them into the object.

> Classes will only receive dependencies that have been properly typehinted or given a default value.

Since the container allows you to bind an implementation to an interface, this gives you great flexibility
to build your classes within the container. If your model class requires a user repository, you can typehint
against a `UserRepositoryInterface` and then bind an implementation to that interface to be passed into
the model when it's created.

```php
class User implements UserRepositoryInterface
{
    // ...snip
}

class UserProfile
{
    protected $user;

    public function __construct(UserRepositoryInterface $user)
    {
        $this->user = $user;
    }
}

// Assume a created $container
$container->set(
    'UserRepositoryInterface',
    function ()
    {
        retur new User;
    }
);

$userProfile = $container->buildObject('UserProfile');

// Use reflection to get the $user property from $userProfile
var_dump($user instanceof User); // prints bool(true)
var_dump($user instanceof UserRepositoryInterface); // prints bool(true)
```

When you build an object, the information required to actually build it (dependencies, etc) are
stored in a callable and set in the container with the class name as the key. You can then fetch
the item from the container by name later on. Alias support applies here as well.

You can also specify to build a shared object by using the function `buildSharedObject($key)`. This
works exactly as you would expect. The information required to build it is discovered, stored in a
callable, then the callable is executed and the result returned. The result is stored as an instance
within the container and is returned on subsequent requests.

<!-- [x] Building an object returns an instance of the requested class -->
<!-- [x] Building a non-shared object returns a new object whenever requested -->
<!-- [x] Building a shared object returns the same object whenever requested -->
<!-- [x] Attempting to build a non-class returns false -->
<!-- [x] Dependencies are resolved from the container's known resources -->
<!-- [x] Resources are created, if they are not present in the container -->
<!-- [x] Dependencies are resolved from their default values -->
<!-- [x] A DependencyResolutionException is thrown, if an object can not be built due to unspecified constructor parameter types -->
<!-- [x] A DependencyResolutionException is thrown, if an object can not be built due to dependency on unknown interfaces -->
<!-- [x] When a circular dependency is detected, a DependencyResolutionException is thrown (Bug #4) -->

### Extending an Item

The Container also allows you to extend items. Extending an item can be thought of as a way to
implement the decorator pattern, although it's not really in the strict sense. When you extend an
item, you must pass the key for the item you want to extend, and then a closure as the second
argument. The closure will receive 2 arguments. The first is result of the callable for the given key,
and the second will be the container itself. When extending an item, the new extended version overwrites
the existing item in the container. If you try to extend an item that does not exist, an `\InvalidArgumentException`
will be thrown.

> When extending an item, normal rules apply. A protected object cannot be overwritten, so you also can not extend them.

```php
// Assume a created $container
$container->set('foo', 'bar');

var_dump($container->get('foo')); // prints string(3) "bar"

$container->extend(
    'foo',
    function ($originalResult, Container $c)
    {
        return $originalResult .= 'baz';
    }
);

var_dump($container->get('foo')); // prints string(6) "barbaz"
```

<!-- [x] An extended resource replaces the original resource -->
<!-- [x] Attempting to extend an undefined resource throws an InvalidArgumentException -->
<!-- [x] A protected resource can not be extended -->

### Service Providers

Another strong feature of the Container is the ability register a _service provider_ to the container.
Service providers are useful in that they are a simple way to encapsulate setup logic for your objects.
In order to use create a service provider, you must implement the `Joomla\DI\ServiceProviderInterface`.
The `ServiceProviderInterface` tells the container that your object has a `register` method that takes
the container as it's only argument.

> Registering service providers is typically done very early in the application lifecycle. Usually
> right after the container is created.

```php
// Assume a created $container
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Database\DatabaseDriver;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->share(
            'Joomla\Database\DatabaseDriver',
            function () use ($container)
            {
                $databaseConfig = (array) $container->get('config')->get('database');

                return new DatabaseDriver($databaseConfig);
            },
            true
        );

        $container->alias('db', 'Joomla\Database\DatabaseDriver');
    }
}

$container->registerServiceProvider(new DatabaseServiceProvider);
```

Here is an alternative using a callable.

```php
// Assume a created $container
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

class CallableServiceProvider implements ServiceProviderInterface
{
    public function getCallable(Container $c)
    {
        return 'something';
    }

    public function register(Container $container)
    {
        $container->set('callable', array($this, 'getCallable');
    }
}

$container->registerServiceProvider(new CallableServiceProvider);
```

The advantage here is that it is easier to write unit tests for the callable method (closures can be awkward to isolate
and test).

<!-- [x] When registering a service provider, its register() method is called with the container instance -->

### Container Aware Objects

You are able to make objects _ContainerAware_ by implementing the `Joomla\DI\ContainerAwareInterface` within your
class. This can be useful when used within the construction level of your application. The construction
level is considered to be anything that is responsible for the creation of other objects. When using
the MVC pattern as recommended by Joomla, this can be at the application or controller level. Controllers
in Joomla are responsible for creating Models and Views, and linking them together. In this case, it would
be reasonable for the controllers to have access to the container in order to build these objects.

> __NOTE:__ The business layer of your app (eg: Models) should _never_ be container aware. Doing so will
> make your code harder to test, and is a far cry from best practices.

### Container Aware Trait

Since PHP 5.4 traits are [available](http://www.php.net/traits), so you can use `ContainerAwareTrait`.

Usage:

```php
use Joomla\DI\ContainerAwareInterface,
	Joomla\DI\ContainerAwareTrait,
	Joomla\Controller\AbstractController;

class MyConroller extends AbstractController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

	public function execute()
	{
		$container = $this->getContainer();
	}
}
```

<!-- [x] Container can be set with setContainer() and retrieved with getContainer() -->
<!-- [x] getContainer() throws an ContainerNotFoundException, if no container is set -->

### Internal Representation of Resources

<!-- [x] The resource supports 'shared' and 'protected' modes, defaulting to 'not shared' and 'not protected' -->
<!-- [x] If a factory is provided, the instance is created on retrieval -->
<!-- [x] If a factory is provided in non-shared mode, the instance is not cached -->
<!-- [x] If a factory is provided i shared mode, the instance is cached -->
<!-- [x] If an instance is provided directly in shared mode, that instance is returned -->
<!-- [x] If an instance is provided directly in non-shared mode, a copy (clone) of that instance is returned -->
<!-- [x] After a reset, a new instance is returned even for shared resources -->

### Exceptions

