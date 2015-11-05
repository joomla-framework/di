<?php
/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;

include_once 'Stubs/stubs.php';

/**
 * Tests for Container class.
 */
class ContainerAccessTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The same resource instance is returned for shared resources
	 */
	public function testGetShared()
	{
		$container = new Container();
		$container->set(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			true
		);

		$this->assertSame($container->get('foo'), $container->get('foo'));
	}

	/**
	 * @testdox A new resource instance is returned for non-shared resources
	 */
	public function testGetNotShared()
	{
		$container = new Container();
		$container->set(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			false
		);

		$this->assertNotSame($container->get('foo'), $container->get('foo'));
	}

	/**
	 * @testdox Accessing an undefined resource throws an InvalidArgumentException
	 * @expectedException  \InvalidArgumentException
	 */
	public function testGetNotExists()
	{
		$container = new Container();
		$container->get('foo');
	}

	/**
	 * @testdox The existence of a resource can be checked
	 */
	public function testExists()
	{
		$container = new Container();
		$container->set('foo', 'bar');

		$this->assertTrue($container->has('foo'), "'foo' should be present");
		$this->assertFalse($container->has('baz'), "'baz' should not be present");
	}

	/**
	 * @testdox getNewInstance() will always return a new instance, even if the resource was set to be shared
	 */
	public function testGetNewInstance()
	{
		$container = new Container();
		$container->share(
			'foo',
			function ()
			{
				return new \stdClass;
			}
		);

		$this->assertNotSame($container->getNewInstance('foo'), $container->getNewInstance('foo'));
	}

	/**
	 * @testdox Getting an object with parameters from the container
	 */
	public function testGetWithParameters()
	{
		$container = new Container();
		$container->set(
			'foo',
			function ($container, $parameter1, $parameter2)
			{
				if (!$container instanceof Container)
					throw new \InvalidArgumentException('Illegal argument, first parameter must be a container!');

				return $parameter1 . ' ' . $parameter2;
			}
		);

		$this->assertSame('joomla di', $container->get('foo', 'joomla', 'di'));
	}

	/**
	 * @testdox Getting a shared item with parameters from the container
	 */
	public function testGetWithParametersShared()
	{
		$container = new Container();
		$container->set(
			'foo',
			function ($container, $parameter1 = null, $parameter2 = null)
			{
				if (!$container instanceof Container)
					throw new \InvalidArgumentException('Illegal argument, first parameter must be a container!');

				return $parameter1 . ' ' . $parameter2;
			},
			true
		);

		$this->assertSame(' ', $container->get('foo'));
		$this->assertSame('joomla di', $container->get('foo', 'joomla', 'di'));
		$this->assertSame('joomla2 di2', $container->get('foo', 'joomla2', 'di2'));
	}

	/**
	 * @testdox Getting an object with object parameters from the container
	 */
	public function testGetWithParametersSharedObjectParameter()
	{
		$container = new Container();
		$container->set(
			'foo',
			function ($container, $object1)
			{
				if (!$container instanceof Container)
					throw new \InvalidArgumentException('Illegal argument, first parameter must be a container!');

				return $object1;
			},
			true
		);

		$object = new \stdClass();
		$object->foo = 'bar';
		$this->assertSame($object, $container->get('foo', $object));
		$this->assertSame($object, $container->get('foo', $object));
		$this->assertSame(null, $container->get('foo', null));
		$this->assertNotSame($object, $container->get('foo', new \stdClass()));
	}

	/**
	 * @testdox Getting an object with object parameters from the container
	 */
	public function testGetWithParametersSharedNoCallable()
	{
		$container = new Container();
		$container->set(
			'foo',
			'bar',
			true
		);

		$this->assertSame('bar', $container->get('foo', 'test'));
		$this->assertSame('bar', $container->get('foo', null));
	}

}
