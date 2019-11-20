<?php
/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\Exception\DependencyResolutionException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

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
	 * @testdox Building a non-shared object whose constructor contains an untyped variadic argument returns a new object whenever requested
	 */
	public function testBuildObjectWithUntypedVariadic()
	{
		$container = new Container();
		$object    = $container->buildObject(StubUntypedVariadic::class);

		$this->assertNotSame($object, $container->get(StubUntypedVariadic::class));
		$this->assertNotSame($container->get(StubUntypedVariadic::class), $container->get(StubUntypedVariadic::class));

		$this->assertEmpty($object->stubs);
	}

	/**
	 * @testdox Building a non-shared object whose constructor contains a typed variadic argument returns a new object whenever requested
	 */
	public function testBuildObjectWithTypedVariadic()
	{
		$container = new Container();
		$object    = $container->buildObject(StubTypedVariadic::class);

		$this->assertNotSame($object, $container->get(StubTypedVariadic::class));
		$this->assertNotSame($container->get(StubTypedVariadic::class), $container->get(StubTypedVariadic::class));

		$this->assertNotEmpty($object->stubs);
		$this->assertContainsOnlyInstancesOf(Stub9::class, $object->stubs);
	}

	/**
	 * @testdox Building a non-shared object whose constructor contains an optional scalar argument returns a new object whenever requested
	 */
	public function testBuildObjectWithOptionalScalar()
	{
		$container = new Container();
		$object    = $container->buildObject(StubOptionalScalar::class);

		$this->assertNotSame($object, $container->get(StubOptionalScalar::class));
		$this->assertNotSame($container->get(StubOptionalScalar::class), $container->get(StubOptionalScalar::class));

		$this->assertTrue($object->enabled);
	}

	/**
	 * @testdox A DependencyResolutionException is thrown, if an object can not be built due to a required scalar constructor parameter
	 */
	public function testBuildObjectWithRequiredScalarThrowsAnException()
	{
		$this->expectException(DependencyResolutionException::class);
		$this->expectExceptionMessage(
			sprintf(
				'Could not resolve the parameter "$enabled" of "%s::__construct()": Scalar parameters cannot be autowired and the parameter does not have a default value.',
				StubRequiredScalar::class
			)
		);

		$container = new Container();
		$object    = $container->buildObject(StubRequiredScalar::class);

		$this->assertNotSame($object, $container->get(StubRequiredScalar::class));
		$this->assertNotSame($container->get(StubRequiredScalar::class), $container->get(StubRequiredScalar::class));
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
		$this->expectExceptionMessage(
			sprintf(
				'Could not resolve the parameter "$stub" of "%s::__construct()": The argument is untyped and has no default value.',
				Stub7::class
			)
		);

		$container = new Container();
		$container->buildObject(Stub7::class);
	}

	/**
	 * @testdox A DependencyResolutionException is thrown, if an object can not be built due to dependency on unknown interfaces
	 */
	public function testGetMethodArgsResolvedIsNotInstanceOfHintedDependency()
	{
		$this->expectException(DependencyResolutionException::class);
		$this->expectExceptionMessage(
			sprintf(
				'Could not resolve the parameter "$stub" of "%s::__construct()": No service for "%s" exists and the dependency could not be autowired.',
				Stub2::class,
				StubInterface::class
			)
		);

		$container = new Container();
		$container->buildObject(Stub2::class);
	}

	/**
	 * @testdox A DependencyResolutionException is thrown, if an object can not be built due to autowiring an unregistered interface
	 */
	public function testGetMethodArgsResolvedIsNotAutowiredForAnUnregisteredInterface()
	{
		$this->expectException(DependencyResolutionException::class);
		$this->expectExceptionMessage(
			sprintf(
				'There is no service for "%s" defined, cannot autowire a class service for an interface.',
				ContainerInterface::class
			)
		);

		$container = new Container();
		$container->buildObject(ContainerInterface::class);
	}

	/**
	 * @testdox A DependencyResolutionException is thrown, if an object can not be built due to autowiring an unregistered abstract class
	 */
	public function testGetMethodArgsResolvedIsNotAutowiredForAnUnregisteredAbstractClass()
	{
		$this->expectException(DependencyResolutionException::class);
		$this->expectExceptionMessage(
			sprintf(
				'There is no service for "%s" defined, cannot autowire an abstract class.',
				AbstractStub::class
			)
		);

		$container = new Container();
		$container->buildObject(AbstractStub::class);
	}

	/**
	 * @testdox When a circular dependency is detected, a DependencyResolutionException is thrown (Bug #4)
	 */
	public function testBug4()
	{
		$fqcn = 'Extension\\vendor\\FooComponent\\FooComponent';

		$this->expectException(DependencyResolutionException::class);
		$this->expectExceptionMessage(
			sprintf(
				'Cannot resolve circular dependency for "%s"',
				$fqcn
			)
		);

		$container = new Container();

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
