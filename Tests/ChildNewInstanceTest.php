<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ContainerResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Tests for getNewInstance() on a child container.
 */
#[CoversClass(Container::class)]
#[CoversClass(ContainerResource::class)]
class ChildNewInstanceTest extends TestCase
{
    #[TestDox("getNewInstance() on a child container does not replace the parent's shared instance")]
    public function testGetNewInstanceInChildKeepsParentInstance()
    {
        $parent = new Container();
        $parent->share('service', static fn () => new \stdClass());

        $shared = $parent->get('service');
        $child  = $parent->createChild();
        $new    = $child->getNewInstance('service');

        $this->assertNotSame($shared, $new);
        $this->assertSame($shared, $parent->get('service'));
        $this->assertSame($shared, $child->get('service'));
    }

    #[TestDox("The parent's factory receives the parent container when a child creates a new instance")]
    public function testGetNewInstanceInChildPassesOwningContainer()
    {
        $parent = new Container();
        $parent->share('service', static fn (Container $container) => $container);

        $this->assertSame($parent, $parent->createChild()->getNewInstance('service'));
    }

    #[TestDox('getNewInstance() on a local resource still replaces the shared instance')]
    public function testGetNewInstanceOnLocalResourceReplacesInstance()
    {
        $container = new Container();
        $container->share('service', static fn () => new \stdClass());

        $shared = $container->get('service');
        $new    = $container->getNewInstance('service');

        $this->assertNotSame($shared, $new);
        $this->assertSame($new, $container->get('service'));
    }

    #[TestDox('createInstance() returns a new instance without caching it')]
    public function testCreateInstance()
    {
        $resource = new ContainerResource(new Container(), static fn () => new \stdClass(), ContainerResource::SHARE);
        $shared   = $resource->getInstance();

        $this->assertNotSame($shared, $resource->createInstance());
        $this->assertSame($shared, $resource->getInstance());
    }
}
