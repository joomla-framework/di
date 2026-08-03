<?php

/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

/**
 * Marker interface for abstract autowiring
 *
 * The abstract autowire concept is used to define a set of resources that can be
 * autowired into a class without having to define them in the constructor.
 *
 * Main purpose is to create a b/c friendly way to build objects with autowiring
 * without changing the constructor for b/c reasons.
 *
 * @since  __DEPLOY_VERSION__
 */
interface AbstractAutowireInterface
{
    /**
     * Get the list of resources that can be autowired into the class.
     *
     * Example:
     *   return ['db'];
     *
     * @return array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getAutowireResources(): array;

    /**
     * Is called with a indexed list of resolved items from the container
     *
     * Example:
     *   if (!empty($resources['db']) {
     *       $this->>setDatabase($resources['db']);
     *   }
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function setAutowireResources(array $resources): void;
}
