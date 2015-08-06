<?php
/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

use Interop\Container\ContainerInterface;
use Joomla\DI\Exception\DependencyResolutionException;
use Joomla\DI\Exception\KeyNotFoundException;
use Joomla\DI\Exception\NotInstantiableException;
use Joomla\DI\Exception\ProtectedKeyException;

/**
 * The Container class.
 */
class Container implements ContainerInterface
{
	/**
	 * Holds the key aliases.
	 *
	 * Format:
	 * 'alias' => 'key'
	 *
	 * @var    array
	 */
	protected $aliases = array();

	/**
	 * Holds the shared instances.
	 *
	 * Format:
	 * 'key' => 'instance' (the value returned by the callback)
	 *
	 * @var    array
	 */
	protected $instances = array();

	/**
	 * Holds the keys, their callbacks, and whether or not the item is meant to be a shared resource.
	 *
	 * Format:
	 * 'key' => array(
	 *     'callback'  => Callable|Closure (function returning a resource instance),
	 *     'shared'    => boolean,
	 *     'protected' => boolean
	 * )
	 *
	 * @var    array
	 */
	protected $dataStore = array();

	/**
	 * Parent for hierarchical containers.
	 *
	 * In fact, this can be any Interop compatible container, which gets decorated by this
	 *
	 * @var    ContainerInterface
	 */
	protected $parent;

	/**
	 * Constructor for the DI Container
	 *
	 * @param   ContainerInterface $parent Parent for hierarchical containers.
	 */
	public function __construct(ContainerInterface $parent = null)
	{
		$this->parent = $parent;
	}

	/**
	 * Retrieve a resource
	 *
	 * @param   string $resourceName Name of the resource to get.
	 *
	 * @return  mixed  The requested resource
	 */
	public function get($resourceName)
	{
		$key = $this->resolveAlias($resourceName);

		if (!isset($this->dataStore[$key]))
		{
			if (is_object($this->parent) && $this->parent->has($key))
			{
				return $this->parent->get($key);
			}

			throw new KeyNotFoundException(sprintf("Resource '%s' has not been registered with the container.", $resourceName));
		}

		if ($this->dataStore[$key]['shared'])
		{
			if (!isset($this->instances[$key]))
			{
				$this->instances[$key] = $this->dataStore[$key]['callback']($this);
			}

			return $this->instances[$key];
		}

		return $this->dataStore[$key]['callback']($this);
	}

	/**
	 * Check if specified resource exists.
	 *
	 * @param   string $resourceName Name of the resource to check.
	 *
	 * @return  boolean  true if key is defined, false otherwise
	 */
	public function has($resourceName)
	{
		$key = $this->resolveAlias($resourceName);

		if (!isset($this->dataStore[$key]))
		{
			if (is_object($this->parent))
			{
				return $this->parent->has($key);
			}

			return false;
		}

		return true;
	}

	/**
	 * Create an alias for a given key for easy access.
	 *
	 * @param   string $alias The alias name
	 * @param   string $key   The key to alias
	 *
	 * @return $this for chaining
	 */
	public function alias($alias, $key)
	{
		$this->aliases[$alias] = $key;

		return $this;
	}

	/**
	 * Resolve a resource name.
	 *
	 * If the resource name is an alias, the corresponding key is returned.
	 * If the resource name is not an alias, the resource name is returned unchanged.
	 *
	 * @param   string $resourceName The key to search for.
	 *
	 * @return  string
	 */
	protected function resolveAlias($resourceName)
	{
		if (isset($this->aliases[$resourceName]))
		{
			return $this->aliases[$resourceName];
		}

		return $resourceName;
	}

	/**
	 * Check whether a resource is shared
	 *
	 * @param   string $resourceName Name of the resource to check.
	 *
	 * @return bool
	 */
	public function isShared($resourceName)
	{
		return $this->hasFlag($resourceName, 'shared');
	}

	/**
	 * Check whether a resource is protected
	 *
	 * @param   string $resourceName Name of the resource to check.
	 *
	 * @return bool
	 */
	public function isProtected($resourceName)
	{
		return $this->hasFlag($resourceName, 'protected');
	}

	/**
	 * Check whether a flag (i.e., one of 'shared' or 'protected') is set
	 *
	 * @param   string $resourceName
	 * @param   string $flag
	 *
	 * @return bool
	 * @throws KeyNotFoundException if key is not defined
	 */
	private function hasFlag($resourceName, $flag)
	{
		$key = $this->resolveAlias($resourceName);

		if (isset($this->dataStore[$key]))
		{
			return $this->dataStore[$key][$flag];
		}

		if ($this->parent instanceof Container)
		{
			$method = 'is' . ucfirst($flag);

			return call_user_func(array($this->parent, $method), $key);
		}

		if ($this->parent instanceof ContainerInterface)
		{
			// We don't know, if parent supports the 'shared' or 'protected' concept, so we assume 'shared' and 'protected'
			return true;
		}

		throw new KeyNotFoundException(sprintf("Resource '%s' has not been registered with the container.", $resourceName));
	}

	/**
	 * Build an object of the requested class
	 *
	 * Creates an instance of the class specified by $resourceName with all dependencies injected.
	 * If the dependencies cannot be completely resolved, a DependencyResolutionException is thrown.
	 *
	 * @param   string  $resourceName The class name to build.
	 * @param   boolean $shared       True to create a shared resource.
	 *
	 * @return  mixed  An object if the class exists and false otherwise
	 * @throws  DependencyResolutionException if the object could not be built (due to missing information)
	 */
	public function buildObject($resourceName, $shared = false)
	{
		static $buildStack = array();

		$key = $this->resolveAlias($resourceName);

		if (in_array($key, $buildStack))
		{
			$buildStack = array();

			throw new DependencyResolutionException("Can't resolve circular dependency");
		}

		array_push($buildStack, $key);

		if ($this->has($key))
		{

			$resource = $this->get($key);
			array_pop($buildStack);

			return $resource;
		}

		try
		{
			$reflection = new \ReflectionClass($key);
		} catch (\ReflectionException $e)
		{
			array_pop($buildStack);

			return false;
		}

		if (!$reflection->isInstantiable())
		{
			$buildStack = array();

			throw new DependencyResolutionException("$key can not be instantiated.");
		}

		$constructor = $reflection->getConstructor();

		if (is_null($constructor))
		{
			// There is no constructor, just return a new object.
			$callback = function () use ($key)
			{
				return new $key;
			};
		}
		else
		{
			$newInstanceArgs = $this->getMethodArgs($constructor);

			$callback = function () use ($reflection, $newInstanceArgs)
			{
				return $reflection->newInstanceArgs($newInstanceArgs);
			};
		}
		$this->set($key, $callback, $shared);

		$resource = $this->get($key);
		array_pop($buildStack);

		return $resource;
	}

	/**
	 * Convenience method for building a shared object.
	 *
	 * @param   string $resourceName The class name to build.
	 *
	 * @return  object  Instance of class specified by $resourceName with all dependencies injected.
	 */
	public function buildSharedObject($resourceName)
	{
		return $this->buildObject($resourceName, true);
	}

	/**
	 * Create a child Container with a new property scope that has the ability to access the parent scope when resolving.
	 *
	 * @return  Container  A new container with the current as a parent
	 */
	public function createChild()
	{
		return new static($this);
	}

	/**
	 * Extend a defined service Closure by wrapping the existing one with a new Closure.  This
	 * works very similar to a decorator pattern.  Note that this only works on service Closures
	 * that have been defined in the current Provider, not parent providers.
	 *
	 * @param   string   $key      The unique identifier for the Closure or property.
	 * @param   \Closure $callable A Closure to wrap the original service Closure.
	 *
	 * @return  void
	 * @throws  \InvalidArgumentException
	 */
	public function extend($key, \Closure $callable)
	{
		$key = $this->resolveAlias($key);

		$raw = $this->getRawGuarded($key);

		$closure = function ($c) use ($callable, $raw)
		{
			return $callable($raw['callback']($c), $c);
		};

		$this->set($key, $closure, $raw['shared']);
	}

	/**
	 * Build an array of constructor parameters.
	 *
	 * @param   \ReflectionMethod $method Method for which to build the argument array.
	 *
	 * @return  array  Array of arguments to pass to the method.
	 * @throws  DependencyResolutionException
	 */
	private function getMethodArgs(\ReflectionMethod $method)
	{
		$methodArgs = array();

		foreach ($method->getParameters() as $param)
		{
			$dependency        = $param->getClass();
			$dependencyVarName = $param->getName();

			// If we have a dependency, that means it has been type-hinted.
			if (!is_null($dependency))
			{
				$dependencyClassName = $dependency->getName();

				// If the dependency class name is registered with this container or a parent, use it.
				if ($this->getRaw($dependencyClassName) !== null)
				{
					$depObject = $this->get($dependencyClassName);
				}
				else
				{
					$depObject = $this->buildObject($dependencyClassName);
				}

				if ($depObject instanceof $dependencyClassName)
				{
					$methodArgs[] = $depObject;
					continue;
				}
			}

			// Finally, if there is a default parameter, use it.
			if ($param->isOptional())
			{
				$methodArgs[] = $param->getDefaultValue();
				continue;
			}

			// Couldn't resolve dependency, and no default was provided.
			throw new DependencyResolutionException(sprintf('Could not resolve dependency: %s', $dependencyVarName));
		}

		return $methodArgs;
	}

	/**
	 * Set a resource
	 *
	 * @param   string  $key       Name of dataStore key to set.
	 * @param   mixed   $value     Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean $shared    True to create and store a shared instance.
	 * @param   boolean $protected True to protect this item from being overwritten. Useful for services.
	 *
	 * @return $this for chaining
	 *
	 * @throws  ProtectedKeyException  Thrown if the provided key is already set and is protected.
	 */
	public function set($key, $value, $shared = false, $protected = false)
	{
		$key = $this->resolveAlias($key);

		if (isset($this->dataStore[$key]) && $this->dataStore[$key]['protected'] === true)
		{
			throw new ProtectedKeyException(sprintf('Key %s is protected and can\'t be overwritten.', $key));
		}

		// If the provided $value is not a closure, make it one now for easy resolution.
		if (!is_callable($value))
		{
			$value = function () use ($value)
			{
				return $value;
			};
		}

		$this->dataStore[$key] = array(
			'callback'  => $value,
			'shared'    => $shared,
			'protected' => $protected
		);

		return $this;
	}

	/**
	 * Convenience method for creating protected keys.
	 *
	 * @param   string   $key      Name of dataStore key to set.
	 * @param   callable $callback Callable function to run when requesting the specified $key.
	 * @param   bool     $shared   True to create and store a shared instance.
	 *
	 * @return $this for chaining
	 */
	public function protect($key, $callback, $shared = false)
	{
		return $this->set($key, $callback, $shared, true);
	}

	/**
	 * Convenience method for creating shared keys.
	 *
	 * @param   string   $key       Name of dataStore key to set.
	 * @param   callable $callback  Callable function to run when requesting the specified $key.
	 * @param   bool     $protected True to create and store a shared instance.
	 *
	 * @return $this for chaining
	 */
	public function share($key, $callback, $protected = false)
	{
		return $this->set($key, $callback, true, $protected);
	}

	/**
	 * Recreate the instance for the specified $key (shared only)
	 *
	 * @param   string $key Name of the dataStore key to get.
	 *
	 * @return  bool  true, if the instance was recreated, false otherwise
	 *
	 * @throws  \InvalidArgumentException
	 */
	private function recreate($key)
	{
		$key = $this->resolveAlias($key);

		$raw = $this->getRawGuarded($key);

		if ($raw['shared'])
		{
			$this->instances[$key] = $raw['callback']($this);

			return true;
		}

		return false;
	}

	/**
	 * Get the raw data assigned to a key.
	 *
	 * @param   string $key The key for which to get the stored item.
	 *
	 * @return  mixed
	 */
	private function getRaw($key)
	{
		if (isset($this->dataStore[$key]))
		{
			return $this->dataStore[$key];
		}
		elseif ($this->parent instanceof Container)
		{
			return $this->parent->getRaw($key);
		}

		return null;
	}

	/**
	 * Method to force the container to return a new instance
	 * of the results of the callback for requested $key.
	 *
	 * @param   string $key Name of the dataStore key to get.
	 *
	 * @return  mixed   Results of running the $callback for the specified $key.
	 */
	public function getNewInstance($key)
	{
		$key = $this->resolveAlias($key);

		$this->recreate($key);

		return $this->get($key);
	}

	/**
	 * Register a service provider to the container.
	 *
	 * @param   ServiceProviderInterface $provider The service provider to register.
	 *
	 * @return  Container  This object for chaining.
	 */
	public function registerServiceProvider(ServiceProviderInterface $provider)
	{
		$provider->register($this);

		return $this;
	}

	/**
	 * @param $key
	 *
	 * @return array
	 */
	private function getRawGuarded($key)
	{
		$raw = $this->getRaw($key);

		if (is_null($raw))
		{
			throw new KeyNotFoundException(sprintf('Key %s has not been registered with the container.', $key));
		}

		return $raw;
	}
}
