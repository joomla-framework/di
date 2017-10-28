<?php
/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ContainerAwareTrait class.
 */
class ContainerAwareTraitTest extends TestCase
{
	/** @var    \Joomla\DI\ContainerAwareTrait */
	protected $object;

	/**
	 * @testdox Container can be set with setContainer()
	 */
	public function testGetContainer()
	{
		$container = new Container();
		$trait     = $this->getObjectForTrait('\\Joomla\\DI\\ContainerAwareTrait');
		$trait->setContainer($container);

		$this->assertAttributeSame($container, 'container', $trait);

		$method = new \ReflectionMethod($trait, 'getContainer');
		$method->setAccessible(true);

		$this->assertSame($container, $method->invokeArgs($trait, []));
	}

	/**
	 * @testdox getContainer() throws an ContainerNotFoundException, if no container is set
	 * @expectedException   \Joomla\DI\Exception\ContainerNotFoundException
	 */
	public function testGetContainerException()
	{
		$trait = $this->getObjectForTrait('\\Joomla\\DI\\ContainerAwareTrait');

		$method = new \ReflectionMethod($trait, 'getContainer');
		$method->setAccessible(true);

		$method->invokeArgs($trait, []);
	}
}
