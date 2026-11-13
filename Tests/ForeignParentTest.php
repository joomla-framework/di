<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ContainerResource;
use Joomla\DI\Exception\KeyNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Tests for the access to the services of an arbitrary PSR-11 parent container.
 */
#[CoversClass(Container::class)]
#[UsesClass(ContainerResource::class)]
class ForeignParentTest extends TestCase
{
    #[TestDox('Autowiring fetches a dependency from a PSR-11 parent container only once')]
    public function testAutowiringFetchesParentServiceOnce()
    {
        $parent    = new CountingContainer();
        $container = new Container($parent);

        $object = $container->buildObject(ForeignParentConsumer::class);

        $this->assertInstanceOf(ForeignParentDependency::class, $object->dependency);
        $this->assertSame(1, $parent->calls);
    }

    #[TestDox('Getting the resource of a PSR-11 parent service does not create the service')]
    public function testGetResourceIsLazy()
    {
        $parent    = new CountingContainer();
        $container = new Container($parent);

        $resource = $container->getResource(ForeignParentDependency::class);

        $this->assertSame(0, $parent->calls);
        $this->assertInstanceOf(ForeignParentDependency::class, $resource->getInstance());
        $this->assertSame(1, $parent->calls);
    }

    #[TestDox('A callable service of a PSR-11 parent is returned, not called, by its resource')]
    public function testCallableParentServiceIsNotCalled()
    {
        $parent    = new CountingContainer();
        $container = new Container($parent);

        $this->assertSame('strtoupper', $container->getResource('callable')->getInstance());
    }
}

// @codingStandardsIgnoreStart

class ForeignParentDependency
{
}

class ForeignParentConsumer
{
    public $dependency;

    public function __construct(ForeignParentDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}

/**
 * A PSR-11 container returning a new object on every get() and counting the calls
 */
class CountingContainer implements ContainerInterface
{
    public $calls = 0;

    public function get(string $id)
    {
        if ($id === 'callable') {
            return 'strtoupper';
        }

        if ($id !== ForeignParentDependency::class) {
            throw new KeyNotFoundException();
        }

        $this->calls++;

        return new ForeignParentDependency();
    }

    public function has(string $id): bool
    {
        return $id === ForeignParentDependency::class || $id === 'callable';
    }
}
