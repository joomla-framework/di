<?php

/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

use Joomla\DI\Exception\ContainerNotFoundException;

/**
 * Defines the trait for a Container Aware Class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait AbstractAutowireSetterAwareTrait
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
    public static function getAutowireResources(): array {
        return [];
    }

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
    public function setAutowireResources(array $resources): void {
        $autowire = static::getAutowireResources();

        foreach ($autowire as $setter => $name) {
            if (!isset($resources[$name])) {
                throw new ContainerNotFoundException(sprintf('Resource "%s" not found in container.', $name));
            }

            if (!method_exists(static::class, $setter)) {
                throw new \RuntimeException(sprintf('Setter method "%s" not found in class "%s".', $setter, static::class));
            }

            $this->$setter($resources[$name]);
        }
    }
}
