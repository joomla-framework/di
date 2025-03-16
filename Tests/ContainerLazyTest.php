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

    /**
     * @testdox  Create Lazy Ghost with arguments
     *
     * @covers   Joomla\DI\Container
     */
    public function testCreateGhostProxyWithArguments()
    {
        $container = new Container();
        $container->set(Stub1::class, function () {
            return new Stub1();
        });
        $container->set(Stub2::class, function ($container) {
            return new Stub2($container->get(Stub1::class));
        });

        $container->lazy(Stub3::class, null, [
            Stub1::class,
            Stub2::class,
        ]);

        $resource = $container->get(Stub3::class);

        $this->assertTrue($resource instanceof Stub3);
        $this->assertTrue($resource->stub1 instanceof Stub1);
        $this->assertTrue($resource->stub2 instanceof Stub2);
    }

    /**
     * @testdox  Create Lazy Ghost without arguments
     *
     * @covers   Joomla\DI\Container
     */
    public function testCreateGhostProxyWithoutArguments()
    {
        $container = new Container();
        $container->set(StubInterface::class, function () {
            return new Stub1();
        });

        $container->lazy(Stub2::class);

        $resource = $container->get(Stub2::class);

        $this->assertTrue($resource instanceof Stub2);
        $this->assertTrue($resource->stub instanceof Stub1);
    }
}
