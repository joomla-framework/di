<?php

/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

/**
 * Defines the representation of a resource.
 *
 * @since  2.0.0
 * @internal
 */
final class ContainerResource
{
    /**
     * Defines the resource as non-shared
     *
     * @const  integer
     * @since  2.0.0
     */
    public const NO_SHARE = 0;

    /**
     * Defines the resource as shared
     *
     * @const  integer
     * @since  2.0.0
     */
    public const SHARE = 1;

    /**
     * Defines the resource as non-protected
     *
     * @const  integer
     * @since  2.0.0
     */
    public const NO_PROTECT = 0;

    /**
     * Defines the resource as protected
     *
     * @const  integer
     * @since  2.0.0
     */
    public const PROTECT = 2;

    /**
     * Defines the resource as non-lazy
     *
     * @const  integer
     * @since  __DEPLOY_VERSION__
     */
    public const NO_LAZY = 0;

    /**
     * Defines the resource as lazy
     *
     * @const  integer
     * @since  __DEPLOY_VERSION__
     */
    public const LAZY = 4;

    /**
     * The container the resource is assigned to
     *
     * @var    Container
     * @since  2.0.0
     */
    private $container;

    /**
     * The object instance for a shared object
     *
     * @var    mixed
     * @since  2.0.0
     */
    private $instance;

    /**
     * The factory object
     *
     * @var    callable
     * @since  2.0.0
     */
    private $factory;

    /**
     * Flag if the resource is shared
     *
     * @var    boolean
     * @since  2.0.0
     */
    private $shared = false;

    /**
     * Flag if the resource is protected
     *
     * @var    boolean
     * @since  2.0.0
     */
    private $protected = false;

    /**
     * Flag if the resource is lazy
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    private $lazy = false;

    /**
     * Create a resource representation
     *
     * @param   Container  $container  The container
     * @param   mixed      $value      The resource or its factory closure
     * @param   integer    $mode       Resource mode, defaults to Resource::NO_SHARE | Resource::NO_PROTECT
     * @param   string     $key        Service key
     *
     * @since   2.0.0
     */
    public function __construct(Container $container, $value, int $mode = 0, string $key = '')
    {
        $this->container = $container;
        $this->shared    = ($mode & self::SHARE) === self::SHARE;
        $this->protected = ($mode & self::PROTECT) === self::PROTECT;
        $this->lazy      = ($mode & self::LAZY) === self::LAZY;

        if (\is_callable($value)) {
            $this->factory = $this->lazy ? $this->lazyFactory($key, $value) : $value;
        } else {
            if ($this->shared) {
                $this->instance = $value;
            }

            if (\is_object($value)) {
                $this->factory = function () use ($value) {
                    return clone $value;
                };
            } else {
                $this->factory = function () use ($value) {
                    return $value;
                };
            }
        }
    }

    /**
     * Check whether the resource is shared
     *
     * @return  boolean
     *
     * @since   2.0.0
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * Check whether the resource is protected
     *
     * @return  boolean
     *
     * @since   2.0.0
     */
    public function isProtected(): bool
    {
        return $this->protected;
    }

    /**
     * Check whether the resource is lazy
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isLazy(): bool
    {
        return $this->lazy;
    }

    /**
     * Get an instance of the resource
     *
     * If a factory was provided, the resource is created and - if it is a shared resource - cached internally.
     * If the resource was provided directly, that resource is returned.
     *
     * @return  mixed
     *
     * @since   2.0.0
     */
    public function getInstance()
    {
        $callable = $this->factory;

        if ($this->isShared()) {
            if ($this->instance === null) {
                $this->instance = $callable($this->container);
            }

            return $this->instance;
        }

        return $callable($this->container);
    }

    /**
     * Get the factory
     *
     * @return  callable
     *
     * @since   2.0.0
     */
    public function getFactory(): callable
    {
        return $this->factory;
    }

    /**
     * Reset the resource
     *
     * The instance cache is cleared, so that the next call to get() returns a new instance.
     * This has an effect on shared, non-protected resources only.
     *
     * @return  boolean  True if the resource was reset, false otherwise
     *
     * @since   2.0.0
     */
    public function reset(): bool
    {
        if ($this->isShared() && !$this->isProtected()) {
            $this->instance = null;

            return true;
        }

        return false;
    }

    /**
     * Create lazy proxy factory.
     *
     * @param   string    $class    Fully qualified class name.
     * @param   callable  $factory  Factory to create lazy proxies for.
     *
     * @return  callable
     *
     * @since   __DEPLOY_VERSION__
     */
    private function lazyFactory(string $class, callable $factory): callable
    {
        if (PHP_VERSION_ID < 80400) {
            return $factory;
        }

        return function () use ($class, $factory) {
            return (new \ReflectionClass($class))->newLazyProxy(fn() => $factory($this->container));
        };
    }
}
