<?php

/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/Stubs/stubs.php';

/**
 * Tests for Container class.
 */
class ContainerLazyTest extends TestCase
{
    /**
     * @testdox  Create Lazy Proxy
     *
     * @covers   Joomla\DI\Container
     */
    public function testCreateLazyProxy()
    {
        $container = new Container();
        $container->lazy(Stub6::class, function () {
            return new Stub6();
        });

        $resource = $container->get(Stub6::class);

        $this->assertTrue($resource instanceof Stub6);
    }
}
