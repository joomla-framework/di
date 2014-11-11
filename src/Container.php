<?php
/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

use Joomla\DI\Exception\DependencyResolutionException;

/**
 * The Container class.
 *
 * @since  1.0
 */
class Container
{
	/**
	 * Policy flag that forces instances to be locked after first get
	 */
	const LOCK_AFTER_GET = 0x00000001;

	/**
	 * Policy flag that drops shared instances on rewrite
	 */
	const DROP_SHARE_ON_REWRITE = 0x00000010;

	/**
	 * Holds the key aliases.
	 *
	 * @var    array  $aliases
	 * @since  1.0
	 */
	protected $aliases = array();

	/**
	 * Holds the shared instances.
	 *
	 * @var    array  $instances
	 * @since  1.0
	 */
	protected $instances = array();

	/**
	 * Holds the keys, their callbacks, and whether or not
	 * the item is meant to be a shared resource.
	 *
	 * @var    array  $dataStore
	 * @since  1.0
	 */
	protected $dataStore = array();

	/**
	 * Parent for hierarchical containers.
	 *
	 * @var    Container
	 * @since  1.0
	 */
	protected $parent;

	/**
	 * Indicates container policy on handling objects
	 *
	 * @var    integet
	 * @since  2.0
	 */
	protected $policy;

	/**
	 * Constructor for the DI Container
	 *
	 * @param   Container  $parent  Parent for hierarchical containers.
	 * @param   integer    $policy  Bitmask of policies to apply
	 *
	 * @since   1.0
	 */
	public function __construct(Container $parent = null, $policy = 0x00000011)
	{
		$this->parent = $parent;
		$this->policy = $policy;
	}

	/**
	 * Create an alias for a given key for easy access.
	 *
	 * @param   string  $alias  The alias name
	 * @param   string  $key    The key to alias
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function alias($alias, $key)
	{
		$this->aliases[$alias] = $key;

		return $this;
	}

	/**
	 * Search the aliases property for a matching alias key.
	 *
	 * @param   string  $key  The key to search for.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function resolveAlias($key)
	{
		if (isset($this->aliases[$key]))
		{
			return $this->aliases[$key];
		}

		return $key;
	}

	/**
	 * Build an object of class $key;
	 *
	 * @param   string   $key     The class name to build.
	 * @param   boolean  $shared  True to create a shared resource.
	 *
	 * @return  mixed  Instance of class specified by $key with all dependencies injected.
	 *                 Returns an object if the class exists and false otherwise
	 *
	 * @since   1.0
	 */
	public function buildObject($key, $shared = false)
	{
		try
		{
			$reflection = new \ReflectionClass($key);
		}
		catch (\ReflectionException $e)
		{
			return false;
		}

		$constructor = $reflection->getConstructor();

		// If there are no parameters, just return a new object.
		if (is_null($constructor))
		{
			$callback = function () use ($key) {
				return new $key;
			};
		}
		else
		{
			$newInstanceArgs = $this->getMethodArgs($constructor);

			// Create a callable for the dataStore
			$callback = function () use ($reflection, $newInstanceArgs) {
				return $reflection->newInstanceArgs($newInstanceArgs);
			};
		}

		return $this->set($key, $callback, $shared)->get($key);
	}

	/**
	 * Convenience method for building a shared object.
	 *
	 * @param   string  $key  The class name to build.
	 *
	 * @return  object  Instance of class specified by $key with all dependencies injected.
	 *
	 * @since   1.0
	 */
	public function buildSharedObject($key)
	{
		return $this->buildObject($key, true);
	}

	/**
	 * Create a child Container with a new property scope that
	 * that has the ability to access the parent scope when resolving.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function createChild()
	{
		return new static($this, $this->policy);
	}

	/**
	 * Extend a defined service Closure by wrapping the existing one with a new Closure.  This
	 * works very similar to a decorator pattern.  Note that this only works on service Closures
	 * that have been defined in the current Provider, not parent providers.
	 *
	 * @param   string    $key       The unique identifier for the Closure or property.
	 * @param   \Closure  $callable  A Closure to wrap the original service Closure.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function extend($key, \Closure $callable)
	{
		if (isset($this->dataStore[$key]) && $this->dataStore[$key]['locked'] === true)
		{
			throw new \OutOfBoundsException(sprintf('Key %s is locked and can\'t be extended.', $key));
		}

		// Get the instance with ignoring the lock
		$raw = $this->getRaw($key, true);

		if (is_null($raw))
		{
			throw new \InvalidArgumentException(sprintf('The requested key %s does not exist to extend.', $key));
		}

		$closure = function ($c) use($callable, $raw) {
			return $callable($raw['callback']($c), $c);
		};

		$this->set($key, $closure, $raw['shared']);
	}

	/**
	 * Build an array of constructor parameters.
	 *
	 * @param   \ReflectionMethod  $method  Method for which to build the argument array.
	 *
	 * @return  array  Array of arguments to pass to the method.
	 *
	 * @since   1.0
	 * @throws  DependencyResolutionException
	 */
	protected function getMethodArgs(\ReflectionMethod $method)
	{
		$methodArgs = array();

		foreach ($method->getParameters() as $param)
		{
			$dependency = $param->getClass();
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
	 * Method to set the key and callback to the dataStore array.
	 *
	 * @param   string   $key        Name of dataStore key to set.
	 * @param   mixed    $value      Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean  $shared     True to create and store a shared instance.
	 * @param   boolean  $protected  True to protect this item from being overwritten. Useful for services.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @throws  \OutOfBoundsException  Thrown if the provided key is already set and is protected.
	 *
	 * @since   1.0
	 */
	public function set($key, $value, $shared = false, $protected = false)
	{
		if (isset($this->dataStore[$key]))
		{
			if ($this->dataStore[$key]['protected'] === true)
			{
				throw new \OutOfBoundsException(sprintf('Key %s is protected and can\'t be overwritten.', $key));
			}
			elseif ($this->dataStore[$key]['locked'] === true)
			{
				throw new \OutOfBoundsException(sprintf('Key %s is locked and can\'t be overwritten.', $key));
			}
			elseif ($this->dataStore[$key]['shared'] === true && isset($this->instances[$key]) && ($this->policy & self::DROP_SHARE_ON_REWRITE))
			{
				unset($this->instances[$key]);
			}
		}
		// If the provided $value is not a closure, make it one now for easy resolution.
		if (!is_callable($value))
		{
			$value = function () use ($value) {
				return $value;
			};
		}

		$this->dataStore[$key] = array(
			'callback' => $value,
			'shared' => $shared,
			'protected' => $protected,
			'locked' => false,
		);

		return $this;
	}

	/**
	 * Convenience method for creating protected keys.
	 *
	 * @param   string    $key       Name of dataStore key to set.
	 * @param   callable  $callback  Callable function to run when requesting the specified $key.
	 * @param   bool      $shared    True to create and store a shared instance.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function protect($key, $callback, $shared = false)
	{
		return $this->set($key, $callback, $shared, true);
	}

	/**
	 * Convenience method for creating shared keys.
	 *
	 * @param   string    $key        Name of dataStore key to set.
	 * @param   callable  $callback   Callable function to run when requesting the specified $key.
	 * @param   bool      $protected  True to create and store a shared instance.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function share($key, $callback, $protected = false)
	{
		return $this->set($key, $callback, true, $protected);
	}

	/**
	 * Method to retrieve the results of running the $callback for the specified $key;
	 *
	 * @param   string   $key       Name of the dataStore key to get.
	 * @param   boolean  $forceNew  True to force creation and return of a new instance.
	 *
	 * @return  mixed   Results of running the $callback for the specified $key.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function get($key, $forceNew = false)
	{
		$raw = $this->getRaw($key);

		if (is_null($raw))
		{
			throw new \InvalidArgumentException(sprintf('Key %s has not been registered with the container.', $key));
		}

		if ($raw['shared'])
		{
			if (!isset($this->instances[$key]) || $forceNew)
			{
				$this->instances[$key] = $raw['callback']($this);
			}

			return $this->instances[$key];
		}

		return call_user_func($raw['callback'], $this);
	}

	/**
	 * Method to check if specified dataStore key exists.
	 *
	 * @param   string  $key  Name of the dataStore key to check.
	 *
	 * @return  boolean  True for success
	 *
	 * @since   1.0
	 */
	public function exists($key)
	{
		return (bool) $this->getRaw($key, true);
	}

	/**
	 * Get the raw data assigned to a key.
	 *
	 * @param   string   $key         The key for which to get the stored item.
	 * @param   boolean  $ignoreLock  Allows to disable lock for internal calls
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	protected function getRaw($key, $ignoreLock = false)
	{
		$key = $this->resolveAlias($key);

		if (isset($this->dataStore[$key]))
		{
			if (!$ignoreLock && ($this->policy & self::LOCK_AFTER_GET))
			{
				// Lock the instance so it cannot be further overwritten
				$this->dataStore[$key]['locked'] = true;
			}

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
	 * @param   string  $key  Name of the dataStore key to get.
	 *
	 * @return  mixed   Results of running the $callback for the specified $key.
	 *
	 * @since   1.0
	 */
	public function getNewInstance($key)
	{
		return $this->get($key, true);
	}

	/**
	 * Method to check if a key is shared
	 *
	 * @param   string  $key  Name of the dataStore key to check.
	 *
	 * @return  boolean  TRUE if the key is shared
	 */
	public function isShared($key)
	{
		return (isset($this->dataStore[$key]) && $this->dataStore[$key]['shared'] === true);
	}

	/**
	 * Method to check if a key is protected
	 *
	 * @param   string  $key  Name of the dataStore key to check.
	 *
	 * @return  boolean  TRUE if the key is protected
	 */
	public function isProtected($key)
	{
		return (isset($this->dataStore[$key]) && $this->dataStore[$key]['protected'] === true);
	}

	/**
	 * Method to check if a key is locked
	 *
	 * @param   string  $key  Name of the dataStore key to check.
	 *
	 * @return  boolean  TRUE if the key is locked
	 */
	public function isLocked($key)
	{
		return (isset($this->dataStore[$key]) && $this->dataStore[$key]['locked'] === true);
	}

	/**
	 * Method to get container policies
	 *
	 * @return  integet  A bitmask of policies
	 */
	public function getPolicies()
	{
		return $this->policies;
	}

	/**
	 * Method to check if a policy is active
	 *
	 * @param   integer  $policy  Policy to check
	 *
	 * @return  boolean  TRUE if the policy is active
	 */
	public function hasPolicy($policy)
	{
		return ($policy & $this->policies);
	}

	/**
	 * Register a service provider to the container.
	 *
	 * @param   ServiceProviderInterface  $provider  The service provider to register.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function registerServiceProvider(ServiceProviderInterface $provider)
	{
		$provider->register($this);

		return $this;
	}
}
