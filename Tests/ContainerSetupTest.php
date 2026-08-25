<?php

/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ContainerResource;
use Joomla\DI\Exception\KeyNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/Stubs/stubs.php';

/**
 * Tests for Container class.
 */
#[CoversClass(Container::class)]
#[UsesClass(ContainerResource::class)]
class ContainerSetupTest extends TestCase
{
    /**
     * Callable object method.
     */
    public function callMe()
    {
        return 'called';
    }

    #[TestDox('Resources can be set up with Callables')]
    public function testSetCallable()
    {
        $container = new Container();
        $container->set(
            'foo',
            [$this, 'callMe']
        );

        $this->assertSame('called', $container->get('foo'));
    }

    #[TestDox('Resources can be set up with Closures')]
    public function testSetClosure()
    {
        $container = new Container();
        $container->set(
            'foo',
            static fn() => 'called'
        );

        $this->assertSame('called', $container->get('foo'));
    }

    #[TestDox('Resources can be scalar values')]
    public function testSetNotCallable()
    {
        $container = new Container();
        $container->set('foo', 'bar');

        $this->assertSame('bar', $container->get('foo'));
    }

    #[TestDox('Setting an existing protected resource throws an OutOfBoundsException')]
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

    #[TestDox('Setting an existing non-protected resource replaces the resource')]
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

    #[TestDox("Default mode is 'not shared' and 'not protected'")]
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

    #[DataProvider('dataForSetFlags')]
    #[TestDox("'shared' and 'protected' mode can be set independently")]
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

    #[TestDox('The convenience method protect() sets resources as protected, but not as shared by default')]
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

    #[TestDox('The convenience method protect() sets resources as shared when passed true as third arg')]
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

    #[TestDox('The convenience method share() sets resources as shared, but not as protected by default')]
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

    #[TestDox('The convenience method share() sets resources as protected when passed true as third arg')]
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

    #[TestDox('The callback gets the container instance as a parameter')]
    public function testGetPassesContainerInstanceShared()
    {
        $container = new Container();
        $container->set(
            'foo',
            static fn(Container $c) => $c
        );

        $this->assertSame($container, $container->get('foo'));
    }

    #[TestDox('The setting an object and then setting it again as null should remove the object')]
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

    #[TestDox('Create Lazy Proxy')]
    public function testCreateLazyProxy()
    {
        $container = new Container();
        $container->set(Stub6::class, $container->lazy(Stub6::class, fn() => new Stub6()));

        $resource = $container->get(Stub6::class);

        $this->assertTrue($resource instanceof Stub6);
    }

    #[RequiresPhp('>= 8.4.0')]
    #[TestDox('If the resource is created with a proxy class')]
    public function testGetLazyProxyInstance()
    {
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
