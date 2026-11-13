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

/**
 * Tests for the access to the resources of a parent container.
 */
#[CoversClass(Container::class)]
#[UsesClass(ContainerResource::class)]
class ChildResourceTest extends TestCase
{
    #[TestDox('Getting an unknown resource from a child container with $bail set throws a KeyNotFoundException')]
    public function testGetResourceBailsInChild()
    {
        $child = (new Container())->createChild();

        $this->expectException(KeyNotFoundException::class);

        $child->getResource('missing', true);
    }

    #[TestDox('Extending an unknown resource in a child container throws a KeyNotFoundException')]
    public function testExtendUnknownResourceInChild()
    {
        $child = (new Container())->createChild();

        $this->expectException(KeyNotFoundException::class);

        $child->extend('missing', static fn ($instance) => $instance);
    }

    #[TestDox('Getting a new instance of an unknown resource from a child container throws a KeyNotFoundException')]
    public function testGetNewInstanceOfUnknownResourceInChild()
    {
        $child = (new Container())->createChild();

        $this->expectException(KeyNotFoundException::class);

        $child->getNewInstance('missing');
    }

    #[TestDox("The resource of a parent's alias can be retrieved from a child container")]
    public function testGetResourceResolvesParentAlias()
    {
        $parent = new Container();
        $parent->set('real', 'value');
        $parent->alias('alias', 'real');

        $this->assertSame($parent->getResource('real'), $parent->createChild()->getResource('alias'));
    }

    #[TestDox("A resource can be extended in a child container through the parent's alias")]
    public function testExtendParentAliasInChild()
    {
        $parent = new Container();
        $parent->set('real', 'value');
        $parent->alias('alias', 'real');

        $child = $parent->createChild();
        $child->extend('alias', static fn ($value) => $value . ' extended');

        $this->assertSame('value extended', $child->get('alias'));
        $this->assertSame('value', $parent->get('alias'));
    }
}
