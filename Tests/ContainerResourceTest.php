<?php

/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ContainerResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/Stubs/stubs.php';

/**
 * Tests for ContainerResource class.
 */
#[CoversClass(ContainerResource::class)]
#[UsesClass(Container::class)]
class ContainerResourceTest extends TestCase
{
    public static function dataInstantiation(): array
    {
        return [
            'shared, protected' => [
                'mode'      => ContainerResource::SHARE | ContainerResource::PROTECT,
                'shared'    => true,
                'protected' => true,
            ],
            'shared, not protected (explicit)' => [
                'mode'      => ContainerResource::SHARE | ContainerResource::NO_PROTECT,
                'shared'    => true,
                'protected' => false,
            ],
            'not shared, protected (explicit)' => [
                'mode'      => ContainerResource::NO_SHARE | ContainerResource::PROTECT,
                'shared'    => false,
                'protected' => true,
            ],
            'not shared, not protected (explicit)' => [
                'mode'      => ContainerResource::NO_SHARE | ContainerResource::NO_PROTECT,
                'shared'    => false,
                'protected' => false,
            ],
            'shared, not protected (implicit)' => [
                'mode'      => ContainerResource::SHARE,
                'shared'    => true,
                'protected' => false,
            ],
            'not shared, protected (implicit)' => [
                'mode'      => ContainerResource::PROTECT,
                'shared'    => false,
                'protected' => true,
            ],
            'not shared, not protected (implicit)' => [
                'mode'      => null,
                'shared'    => false,
                'protected' => false,
            ],
        ];
    }

    #[DataProvider('dataInstantiation')]
    #[TestDox("The resource supports 'shared' and 'protected' modes, defaulting to 'not shared' and 'not protected'")]
    public function testInstantiation(?int $mode, bool $shared, bool $protected)
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

    #[TestDox('If a factory is provided, the instance is created on retrieval')]
    public function testGetInstanceWithFactory()
    {
        $container = new Container();

        $resource = new ContainerResource(
            $container,
            static fn() => new Stub6()
        );

        $this->assertInstanceOf(Stub6::class, $resource->getInstance());
    }

    #[TestDox('If a factory is provided in non-shared mode, the instance is not cached')]
    public function testGetInstanceWithFactoryInNonSharedMode()
    {
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            static fn() => new Stub6(),
            ContainerResource::NO_SHARE
        );

        $this->assertNotSame($resource->getInstance(), $resource->getInstance());
    }

    #[TestDox('If a factory is provided in shared mode, the instance is cached')]
    public function testGetInstanceWithFactoryInSharedMode()
    {
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            static fn() => new Stub6(),
            ContainerResource::SHARE
        );

        $this->assertSame($resource->getInstance(), $resource->getInstance());
    }

    #[TestDox('If an instance is provided directly in shared mode, that instance is returned')]
    public function testGetInstanceWithInstanceInSharedMode()
    {
        $stub      = new Stub6();
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            $stub,
            ContainerResource::SHARE
        );

        $this->assertSame($stub, $resource->getInstance());
    }

    #[TestDox('If an instance is provided directly in non-shared mode, a copy (clone) of that instance is returned')]
    public function testGetInstanceWithInstanceInNonSharedMode()
    {
        $stub      = new Stub6();
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            $stub,
            ContainerResource::NO_SHARE
        );

        $this->assertNotSame($stub, $resource->getInstance());
    }

    #[TestDox('After a reset, a new instance is returned even for shared resources with factories')]
    public function testResetWithFactory()
    {
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            static fn() => new Stub6(),
            ContainerResource::SHARE
        );

        $one = $resource->getInstance();

        $resource->reset();

        $two = $resource->getInstance();

        $this->assertNotSame($one, $two);
    }

    #[TestDox('After a reset, a new instance is returned even for shared resources with instances')]
    public function testResetWithInstance()
    {
        $stub      = new Stub6();
        $container = new Container();
        $resource  = new ContainerResource(
            $container,
            $stub,
            ContainerResource::SHARE
        );

        $one = $resource->getInstance();

        $resource->reset();

        $two = $resource->getInstance();

        $this->assertNotSame($one, $two);
    }
}
