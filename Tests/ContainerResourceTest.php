<?php

/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ContainerResource;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/Stubs/stubs.php';

/**
 * Tests for ContainerResource class.
 */
class ContainerResourceTest extends TestCase
{
    /**
     * @testdox  Throws an exception if unknown option provided
     *
     * @covers   Joomla\DI\ContainerResource
     * @uses     Joomla\DI\Container
     */
    public function testUnknownOption()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown option "unknown" given. Allowed options: shared, protected');

        $stub      = new Stub6();
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            $stub,
            [ 'shared' => true, 'unknown' => true ]
        );
    }

    public function dataInstantiation(): \Generator
    {
        yield 'shared, protected' => [
            'mode'      => [ 'shared' => true, 'protected' => true ],
            'shared'    => true,
            'protected' => true,
        ];

        yield 'shared, not protected (explicit)' => [
            'mode'      => ['shared' => true, 'protected' => false],
            'shared'    => true,
            'protected' => false,
        ];

        yield 'not shared, protected (explicit)' => [
            'mode'      => ['shared' => false, 'protected' => true],
            'shared'    => false,
            'protected' => true,
        ];

        yield 'not shared, not protected (explicit)' => [
            'mode'      => ['shared' => false, 'protected' => false],
            'shared'    => false,
            'protected' => false,
        ];

        yield 'shared, not protected (implicit)' => [
            'mode'      => [ 'shared' => true ],
            'shared'    => true,
            'protected' => false,
        ];

        yield 'not shared, protected (implicit)' => [
            'mode'      => [ 'protected' => true ],
            'shared'    => false,
            'protected' => true,
        ];

        yield 'not shared, not protected (implicit)' => [
            'mode'      => [],
            'shared'    => false,
            'protected' => false,
        ];
    }

    /**
     * @testdox  The resource supports 'shared' and 'protected' modes, defaulting to 'not shared' and 'not protected'
     *
     * @covers   Joomla\DI\ContainerResource
     * @uses     Joomla\DI\Container
     *
     * @dataProvider dataInstantiation
     */
    public function testInstantiation(?array $mode, bool $shared, bool $protected)
    {
        $container = new Container();

        if ($mode === null) {
            $descriptor = new ContainerResource($container, 'dummy');
        } else {
            $descriptor = new ContainerResource($container, 'dummy', $mode);
        }

        $this->assertSame($shared, $descriptor->isShared());
        $this->assertSame($protected, $descriptor->isProtected());
    }

    /**
     * @testdox  If a factory is provided, the instance is created on retrieval
     *
     * @covers   Joomla\DI\ContainerResource
     * @uses     Joomla\DI\Container
     */
    public function testGetInstanceWithFactory()
    {
        $container = new Container();

        $resource = new ContainerResource(
            $container,
            static function () {
                return new Stub6();
            }
        );

        $this->assertInstanceOf(Stub6::class, $resource->getInstance());
    }

    /**
     * @testdox  If a factory is provided in non-shared mode, the instance is not cached
     *
     * @covers   Joomla\DI\ContainerResource
     * @uses     Joomla\DI\Container
     */
    public function testGetInstanceWithFactoryInNonSharedMode()
    {
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            static function () {
                return new Stub6();
            },
            [ 'shared' => false ]
        );

        $this->assertNotSame($resource->getInstance(), $resource->getInstance());
    }

    /**
     * @testdox  If a factory is provided in shared mode, the instance is cached
     *
     * @covers   Joomla\DI\ContainerResource
     * @uses     Joomla\DI\Container
     */
    public function testGetInstanceWithFactoryInSharedMode()
    {
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            static function () {
                return new Stub6();
            },
            [ 'shared' => true ]
        );

        $this->assertSame($resource->getInstance(), $resource->getInstance());
    }

    /**
     * @testdox  If an instance is provided directly in shared mode, that instance is returned
     *
     * @covers   Joomla\DI\ContainerResource
     * @uses     Joomla\DI\Container
     */
    public function testGetInstanceWithInstanceInSharedMode()
    {
        $stub      = new Stub6();
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            $stub,
            [ 'shared' => true ]
        );

        $this->assertSame($stub, $resource->getInstance());
    }

    /**
     * @testdox  If an instance is provided directly in non-shared mode, a copy (clone) of that instance is returned
     *
     * @covers   Joomla\DI\ContainerResource
     * @uses     Joomla\DI\Container
     */
    public function testGetInstanceWithInstanceInNonSharedMode()
    {
        $stub      = new Stub6();
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            $stub,
            [ 'shared' => false ]
        );

        $this->assertNotSame($stub, $resource->getInstance());
    }

    /**
     * @testdox  After a reset, a new instance is returned even for shared resources with factories
     *
     * @covers   Joomla\DI\ContainerResource
     * @uses     Joomla\DI\Container
     */
    public function testResetWithFactory()
    {
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            static function () {
                return new Stub6();
            },
            [ 'shared' => true ]
        );

        $one = $resource->getInstance();

        $resource->reset();

        $two = $resource->getInstance();

        $this->assertNotSame($one, $two);
    }

    /**
     * @testdox  After a reset, a new instance is returned even for shared resources with instances
     *
     * @covers   Joomla\DI\ContainerResource
     * @uses     Joomla\DI\Container
     */
    public function testResetWithInstance()
    {
        $stub      = new Stub6();
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            $stub,
            [ 'shared' => true ]
        );

        $one = $resource->getInstance();

        $resource->reset();

        $two = $resource->getInstance();

        $this->assertNotSame($one, $two);
    }
}
