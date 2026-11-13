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
 * Tests for shared resources whose factory returns null.
 */
#[CoversClass(ContainerResource::class)]
class SharedNullResourceTest extends TestCase
{
    #[TestDox('A shared factory returning null is called only once')]
    public function testSharedFactoryReturningNullIsCalledOnce()
    {
        $calls    = 0;
        $resource = new ContainerResource(
            new Container(),
            static function () use (&$calls) {
                $calls++;

                return null;
            },
            ContainerResource::SHARE
        );

        $this->assertNull($resource->getInstance());
        $this->assertNull($resource->getInstance());
        $this->assertSame(1, $calls);
    }

    #[TestDox('After a reset, a shared factory returning null is called again')]
    public function testResetSharedFactoryReturningNull()
    {
        $calls    = 0;
        $resource = new ContainerResource(
            new Container(),
            static function () use (&$calls) {
                $calls++;

                return null;
            },
            ContainerResource::SHARE
        );

        $resource->getInstance();
        $this->assertTrue($resource->reset());
        $resource->getInstance();

        $this->assertSame(2, $calls);
    }
}
