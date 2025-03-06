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
 * Tests for ContainerResourceDefinition class.
 */
class ContainerResourceDefinitionTest extends TestCase
{
    /**
     * @testdox  Defines services in convenient and readable way
     *
     * @covers   Joomla\DI\ContainerResourceDefinition
     */
    public function testDef(): void
    {
        $container = new Container();
        $container->def('foo')
            ->shared()
            ->protected()
            ->aliases(['bar', 'baz'])
            ->tags(['tag1', 'tag2'])
            ->factory(static function () {
                return new Stub1();
            })
            ->end();


        $foo = $container->get('foo');

        $this->assertInstanceOf(Stub1::class, $foo);
        $this->assertTrue($container->isShared('foo'));
        $this->assertTrue($container->isProtected('foo'));

        $this->assertSame($foo, $container->get('bar'));
        $this->assertSame($foo, $container->get('baz'));

        $this->assertSame([ $foo ], $container->getTagged('tag1'));
        $this->assertSame([ $foo ], $container->getTagged('tag2'));
    }

    /**
     * @testdox  Creates factory automatically
     *
     * @covers   Joomla\DI\ContainerResourceDefinition
     */
    public function testDefAutoFactory(): void
    {
        $container = new Container();
        $container->def(Stub1::class)->end();

        $stub1 = $container->get(Stub1::class);

        $this->assertInstanceOf(Stub1::class, $stub1);
    }

    /**
     * @testdox  Defines lazy services
     *
     * @covers   Joomla\DI\ContainerResourceDefinition
     */
    public function testDefLazy(): void
    {
        $container = new Container();

        $container->def(StubInterface::class)
            ->lazy(Stub2::class)
            ->factory(static fn () => new Stub2(new Stub1()))
            ->end();

        $stub1 = $container->get(StubInterface::class);

        if (PHP_VERSION_ID >= 80400) {
            $this->assertTrue((new \ReflectionClass(Stub2::class))->isUninitializedLazyObject($stub1));
        }

        $this->assertInstanceOf(Stub2::class, $stub1);
    }
}
