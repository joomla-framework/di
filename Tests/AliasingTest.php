<?php

/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ContainerResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/Stubs/stubs.php';

/**
 * Tests for Container class.
 */
#[CoversClass(Container::class)]
#[UsesClass(ContainerResource::class)]
class AliasingTest extends TestCase
{
    #[TestDox('Both the original key and the alias return the same resource')]
    public function testResolveAliasSameAsKey()
    {
        $container = new Container();
        $container->set(
            'foo',
            static fn() => new \stdClass(),
            true,
            true
        );
        $container->alias('bar', 'foo');

        $this->assertSame(
            $container->get('foo'),
            $container->get('bar'),
            'When retrieving an alias of a class, both the original and the alias should return the same object instance.'
        );
    }

    #[TestDox('has() also resolves the alias if set.')]
    public function testExistsResolvesAlias()
    {
        $container = new Container();
        $container->set(
            'foo',
            static fn() => new \stdClass(),
            true,
            true
        );
        $container->alias('bar', 'foo');

        $this->assertTrue($container->has('foo'), "Original 'foo' was not resolved");
        $this->assertTrue($container->has('bar'), "Alias 'bar' was not resolved");
    }
}
