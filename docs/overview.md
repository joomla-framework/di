## Overview

The Joomla! Dependency Injection package provides a `container-interop` (upcoming `PSR-11`) compatible
Dependency Injection Container. Additional to the `get()` and `has()` methods defined in the proposed standard,
it supports

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

To create a container, it just has to be instantiated.

```php
use \Joomla\DI\Container;

$container = new Container;
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

<!-- [x] Child container has access to parent's resources -->
<!-- [x] Child container resolves parent's alias to parent's resource -->

### Setup the Container

<!-- [x] Resources can be set up with Callables -->
<!-- [x] Resources can be set up with Closures -->
<!-- [x] Resources can be scalar values -->
<!-- [x] Setting an existing protected resource throws an OutOfBoundsException -->
<!-- [x] Setting an existing non-protected resource replaces the resource -->
<!-- [x] Default mode is 'not shared' and 'not protected' -->
<!-- [x] 'shared' and 'protected' mode can be set independently -->
<!-- [x] The callback gets the container instance as a parameter -->

#### Shared Resources

<!-- [x] The convenience method share() sets resources as shared, but not as protected by default -->
<!-- [x] The convenience method share() sets resources as protected when passed true as third arg -->

#### Protected Resources

<!-- [x] The convenience method protect() sets resources as protected, but not as shared by default -->
<!-- [x] The convenience method protect() sets resources as shared when passed true as third arg -->

### Retrieve Resources

<!-- [x] The same resource instance is returned for shared resources -->
<!-- [x] A new resource instance is returned for non-shared resources -->
<!-- [x] Accessing an undefined resource throws an InvalidArgumentException -->
<!-- [x] The existence of a resource can be checked -->
<!-- [x] getNewInstance() will always return a new instance, even if the resource was set to be shared -->

### Aliasing

<!-- [x] Both the original key and the alias return the same resource -->
<!-- [x] has() also resolves the alias if set. -->
<!-- [x] Resources from an arbitrary Interop compatible container are 'shared' and 'protected' -->

### Building Objects

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

### Decorating (extending) Resources

<!-- [x] An extended resource replaces the original resource -->
<!-- [x] Attempting to extend an undefined resource throws an InvalidArgumentException -->
<!-- [x] A protected resource can not be extended -->

### Service Provider

<!-- [x] When registering a service provider, its register() method is called with the container instance -->

### Making your classes Container aware

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

