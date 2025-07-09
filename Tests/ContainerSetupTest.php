<?php

/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\Exception\KeyNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/Stubs/stubs.php';

/**
 * Tests for Container class.
 */
class ContainerSetupTest extends TestCase
{
    /**
     * Callable object method.
     */
    public function callMe()
    {
        return 'called';
    }

    /**
     * @testdox  Resources can be set up with Callables
     *
     * @covers   \Joomla\DI\Container
     * @uses     \Joomla\DI\ContainerResource
     */
    public function testSetCallable()
    {
        $container = new Container();
        $container->set(
            'foo',
            [$this, 'callMe']
        );

        $this->assertSame('called', $container->get('foo'));
    }

    /**
     * @testdox  Resources can be set up with Closures
     *
     * @covers   \Joomla\DI\Container
     * @uses     \Joomla\DI\ContainerResource
     */
    public function testSetClosure()
    {
        $container = new Container();
        $container->set(
            'foo',
            static fn() => 'called'
        );

        $this->assertSame('called', $container->get('foo'));
    }

    /**
     * @testdox  Resources can be scalar values
     *
     * @covers   \Joomla\DI\Container
     * @uses     \Joomla\DI\ContainerResource
     */
    public function testSetNotCallable()
    {
        $container = new Container();
        $container->set('foo', 'bar');

        $this->assertSame('bar', $container->get('foo'));
    }

    /**
     * @testdox  Setting an existing protected resource throws an OutOfBoundsException
     *
     * @covers   \Joomla\DI\Container
     * @uses     \Joomla\DI\ContainerResource
     */
    public function testSetAlreadySetProtected()
    {
        $this->expectException(\OutOfBoundsException::class);

        $container = new Container();
        $container->set(
            'foo',
            static function () {
            },
            false,
            true
        );
        $container->set(
            'foo',
            static function () {
            },
            false,
            true
        );
    }

    /**
     * @testdox  Setting an existing non-protected resource replaces the resource
     *
     * @covers   \Joomla\DI\Container
     * @uses     \Joomla\DI\ContainerResource
     */
    public function testSetAlreadySetNotProtected()
    {
        $container = new Container();
        $container->set(
            'foo',
            static fn() => 'original'
        );

        $container->set(
            'foo',
            static fn() => 'changed'
        );
        $this->assertSame('changed', $container->get('foo'));
    }

    /**
     * @testdox  Default mode is 'not shared' and 'not protected'
     *
     * @covers   \Joomla\DI\Container
     * @uses     \Joomla\DI\ContainerResource
     */
    public function testSetDefault()
    {
        $container = new Container();
        $container->set(
            'foo',
            static fn() => new \stdClass()
        );

        $this->assertFalse($container->isShared('foo'));
        $this->assertFalse($container->isProtected('foo'));
    }

    public static function dataForSetFlags(): array
    {
        return [
            'shared, protected' => [
                'shared'    => true,
                'protected' => true,
            ],
            'shared, not protected' => [
                'shared'    => true,
                'protected' => false,
            ],
            'not shared, protected' => [
                'shared'    => false,
                'protected' => true,
            ],
            'not shared, not protected' => [
                'shared'    => false,
                'protected' => false,
            ],
        ];
    }

    /**
     * @testdox  'shared' and 'protected' mode can be set independently
     *
     * @covers   \Joomla\DI\Container
     * @uses     \Joomla\DI\ContainerResource
     */
    #[DataProvider('dataForSetFlags')]
    public function testSetSharedProtected(bool $shared, bool $protected)
    {
        $container = new Container();
        $container->set(
            'foo',
            static fn() => new \stdClass(),
            $shared,
            $protected
        );

        $this->assertSame($shared, $container->isShared('foo'));
        $this->assertSame($protected, $container->isProtected('foo'));
    }

    /**
     * @testdox  The convenience method protect() sets resources as protected, but not as shared by default
     *
     * @covers   \Joomla\DI\Container
     * @uses     \Joomla\DI\ContainerResource
     */
    public function testProtect()
    {
        $container = new Container();
        $container->protect(
            'foo',
            static fn() => new \stdClass()
        );

        $this->assertFalse($container->isShared('foo'));
        $this->assertTrue($container->isProtected('foo'));
    }

    /**
     * @testdox  The convenience method protect() sets resources as shared when passed true as third arg
     *
     * @covers   \Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testProtectShared()
    {
        $container = new Container();
        $container->protect(
            'foo',
            static fn() => new \stdClass(),
            true
        );

        $this->assertTrue($container->isShared('foo'));
        $this->assertTrue($container->isProtected('foo'));
    }

    /**
     * @testdox  The convenience method share() sets resources as shared, but not as protected by default
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testShare()
    {
        $container = new Container();
        $container->share(
            'foo',
            static fn() => new \stdClass()
        );

        $this->assertTrue($container->isShared('foo'));
        $this->assertFalse($container->isProtected('foo'));
    }

    /**
     * @testdox  The convenience method share() sets resources as protected when passed true as third arg
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testShareProtected()
    {
        $container = new Container();
        $container->share(
            'foo',
            static fn() => new \stdClass(),
            true
        );

        $this->assertTrue($container->isShared('foo'));
        $this->assertTrue($container->isProtected('foo'));
    }

    /**
     * @testdox  The callback gets the container instance as a parameter
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testGetPassesContainerInstanceShared()
    {
        $container = new Container();
        $container->set(
            'foo',
            static fn(Container $c) => $c
        );

        $this->assertSame($container, $container->get('foo'));
    }

    /**
     * @testdox  The setting an object and then setting it again as null should remove the object
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testSettingNullUnsetsAResource()
    {
        $this->expectException(KeyNotFoundException::class);

        $container = new Container();
        $container->set(
            'foo',
            static fn() => 'original'
        );

        $container->set(
            'foo',
            null
        );

        $container->get('foo');
    }

    /**
     * @testdox  Create Lazy Proxy
     *
     * @covers   Joomla\DI\Container
     */
    public function testCreateLazyProxy()
    {
        $container = new Container();
        $container->set(Stub6::class, $container->lazy(Stub6::class, fn() => new Stub6()));

        $resource = $container->get(Stub6::class);

        $this->assertTrue($resource instanceof Stub6);
    }

    /**
     * @testdox  If the resource is created with a proxy class
     *
     * @uses     Joomla\DI\Container
     */
    public function testGetLazyProxyInstance()
    {
        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped('Lazy objects are only supported in PHP 8.4 or newer.');
        }

        $factoryCalled = false;

        $container = new Container();
        $container->set(Stub6::class, $container->lazy(Stub6::class, function () use (&$factoryCalled) {
            $factoryCalled = true;
            return new Stub6();
        }));

        $resource = $container->get(Stub6::class);

        $this->assertTrue((new \ReflectionClass(Stub6::class))->isUninitializedLazyObject($resource));
        $this->assertFalse($factoryCalled);
    }
}
