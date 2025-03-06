<?php

/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

/**
 * Defines services in convenient and readable way.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContainerResourceDefinition
{
    /**
    * Resource key.
    *
    * @var    string
    * @since  __DEPLOY_VERSION__
    */
    private string $key;

    /**
    * Container for resource.
    *
    * @var    Container
    * @since  __DEPLOY_VERSION__
    */
    private Container $container;

    /**
    * Resource value.
    *
    * @var    mixed
    * @since  __DEPLOY_VERSION__
    */
    private $value = null;

    /**
    * Is resource shared?
    *
    * @var    bool
    * @since  __DEPLOY_VERSION__
    */
    private bool $shared = false;

    /**
    * Is resource protected?
    *
    * @var    bool
    * @since  __DEPLOY_VERSION__
    */
    private bool $protected = false;

    /**
    * Is resource lazy?
    *
    * @var    bool
    * @since  __DEPLOY_VERSION__
    */
    private bool $lazy = false;

    /**
     * Full class name of the resource.
    *
    * @var    ?string
    * @since  __DEPLOY_VERSION__
    */
    private ?string $lazyClass = null;

    /**
    * Resource aliases.
    *
    * @var    array
    * @since  __DEPLOY_VERSION__
    */
    private array $aliases = [];

    /**
    * Resource tags.
    *
    * @var    array
    * @since  __DEPLOY_VERSION__
    */
    private array $tags = [];

    /**
     * Constructor.
     *
     * @param   string     $key        Resource key
     * @param   Container  $container  Resource container
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(string $key, Container $container)
    {
        $this->key = $key;
        $this->container = $container;
    }

    /**
     * Set resource factory.
     *
     * @param   callable  $factory  Resource factory
     *
     * @return  $this
     *
     * @since   __DEPLOY_VERSION__
     */
    public function factory(callable $factory): self
    {
        $this->value = $factory;

        return $this;
    }

    /**
     * Set resource value.
     *
     * @param   mixed  $value  Resource value
     *
     * @return  $this
     *
     * @since   __DEPLOY_VERSION__
     */
    public function value($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Set resource as shared.
     *
     * @return  $this
     *
     * @since   __DEPLOY_VERSION__
     */
    public function shared(): self
    {
        $this->shared = true;

        return $this;
    }

    /**
     * Set resource as protected.
     *
     * @return  $this
     *
     * @since   __DEPLOY_VERSION__
     */
    public function protected(): self
    {
        $this->protected = true;

        return $this;
    }

    /**
     * Set resource as lazy.
     *
     * @param   ?string  $class  Full class name of the resource.
     *
     * @return  $this
     *
     * @since   __DEPLOY_VERSION__
     */
    public function lazy(?string $class = null): self
    {
        $this->lazy = true;

        $this->lazyClass = $class ?? $this->key;

        return $this;
    }

    /**
     * Set resource aliases.
     *
     * @param   array  $aliases  Array of aliases
     *
     * @return  $this
     *
     * @since   __DEPLOY_VERSION__
     */
    public function aliases(array $aliases): self
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * Set resource tags.
     *
     * @param   array  $tags  Array of tags
     *
     * @return  $this
     *
     * @since   __DEPLOY_VERSION__
     */
    public function tags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Apply the resource definition to the container.
     *
     * @return  Container
     *
     * @since   __DEPLOY_VERSION__
     */
    public function end(): Container
    {
        if ($this->lazy) {
            $this->value = $this->container->lazy(
                $this->lazyClass,
                $this->value ?? fn () => new $this->lazyClass(),
            );
        } else {
            $this->value = $this->value ?? fn () => new $this->key();
        }

        $this->container->set($this->key, $this->value, $this->shared, $this->protected);

        foreach ($this->aliases as $alias) {
            $this->container->alias($alias, $this->key);
        }

        foreach ($this->tags as $tag) {
            $this->container->tag($tag, [ $this->key ]);
        }

        return $this->container;
    }
}
