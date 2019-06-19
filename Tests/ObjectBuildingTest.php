<?php
/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\Exception\DependencyResolutionException;
use PHPUnit\Framework\TestCase;

include_once __DIR__.'/Stubs/stubs.php';

/**
 * Tests for Container class.
 */
class ObjectBuildingTest extends TestCase
{
	/**
	 * @testdox Building an object returns an instance of the requested class
	 */
	public function testBuildObjectNoDependencies()
	{
		$container = new Container();
		$object    = $container->buildObject(Stub1::class);

		$this->assertInstanceOf(Stub1::class, $object);
	}

	/**
	 * @testdox Building a non-shared object returns a new object whenever requested
	 */
	public function testBuildObject()
	{
		$container = new Container();
		$object    = $container->buildObject(Stub1::class);

		$this->assertNotSame($object, $container->get(Stub1::class));
		$this->assertNotSame($container->get(Stub1::class), $container->get(Stub1::class));
	}

	/**
	 * @testdox Building a shared object returns the same object whenever requested
	 */
	public function testBuildSharedObject()
	{
		$container = new Container();
		$object    = $container->buildSharedObject(Stub1::class);

		$this->assertSame($object, $container->get(Stub1::class));
		$this->assertSame($container->get(Stub1::class), $container->get(Stub1::class));
	}

	/**
	 * @testdox Attempting to build a non-class returns false
	 */
	public function testBuildObjectNonClass()
	{
		$container = new Container();
		$this->assertFalse($container->buildObject('foobar'));
	}

	/**
	 * @testdox Dependencies are resolved from the container's known resources
	 */
	public function testBuildObjectGetDependencyFromContainer()
	{
		$container = new Container();
		$container->set(
			StubInterface::class, function ()
		{
			return new Stub1;
		});

		$object = $container->buildObject(Stub2::class);

		$this->assertInstanceOf(Stub1::class, $object->stub);
	}

	/**
	 * @testdox Resources are created, if they are not present in the container
	 */
	public function testGetMethodArgsConcreteClass()
	{
		$container = new Container();
		$object = $container->buildObject(Stub5::class);

		$this->assertInstanceOf(Stub4::class, $object->stub);
	}

	/**
	 * @testdox Dependencies are resolved from their default values
	 */
	public function testGetMethodArgsDefaultValues()
	{
		$container = new Container();
		$object    = $container->buildObject(Stub6::class);

		$this->assertEquals('foo', $object->stub);
	}

	/**
	 * @testdox A DependencyResolutionException is thrown, if an object can not be built due to unspecified constructor parameter types
	 */
	public function testGetMethodArgsCantResolve()
	{
		$this->expectException(DependencyResolutionException::class);

		$container = new Container();
		$container->buildObject(Stub7::class);
	}

	/**
	 * @testdox A DependencyResolutionException is thrown, if an object can not be built due to dependency on unknown interfaces
	 */
	public function testGetMethodArgsResolvedIsNotInstanceOfHintedDependency()
	{
		$this->expectException(DependencyResolutionException::class);

		$container = new Container();
		$container->buildObject(Stub2::class);
	}

	/**
	 * @testdox When a circular dependency is detected, a DependencyResolutionException is thrown (Bug #4)
	 */
	public function testBug4()
	{
		$this->expectException(DependencyResolutionException::class);

		$container = new Container();

		$fqcn = 'Extension\\vendor\\FooComponent\\FooComponent';
		$data = [];

		$container->set(
			$fqcn,
			function (Container $c) use ($fqcn, $data)
			{
				$instance = $c->buildObject($fqcn);
				$instance->setData($data);

				return $instance;
			}
		);

		$container->get($fqcn);
	}
}
