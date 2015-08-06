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
class AliasingTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox Both the original key and the alias return the same resource
	 */
	public function testResolveAliasSameAsKey()
	{
		$container = new Container();
		$container->set('foo', function ()
		{
			return new \StdClass;
		}, true, true);
		$container->alias('bar', 'foo');

		$this->assertSame(
			$container->get('foo'),
			$container->get('bar'),
			'When retrieving an alias of a class, both the original and the alias should return the same object instance.'
		);
	}

	/**
	 * @testdox exists() also resolves the alias if set.
	 */
	public function testExistsResolvesAlias()
	{
		$container = new Container();
		$container
			->set('foo', function () {return new \stdClass;}, true, true)
			->alias('bar', 'foo');

		$this->assertTrue($container->has('foo'), "Original 'foo' was not resolved");
		$this->assertTrue($container->has('bar'), "Alias 'bar' was not resolved");
	}

	/**
	 * @testdox Child container resolves parent's alias to parent's resource
	 */
	public function testChildResolveAlias()
	{
		$container = new Container();
		$container->set('Joomla\\DI\\Tests\\StubInterface', function ()
		{
			return new Stub1;
		});
		$container->alias('stub', 'Joomla\\DI\\Tests\\StubInterface');

		$child = $container->createChild();
		$this->assertInstanceOf('Joomla\\DI\\Tests\\Stub1', $child->get('stub'));
	}
}
