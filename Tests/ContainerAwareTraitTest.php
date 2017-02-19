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
	 * @testdox Container can be set with setContainer() and retrieved with getContainer()
	 */
	public function testGetContainer()
	{
		$container = new Container();
		$trait     = $this->getObjectForTrait('\\Joomla\\DI\\ContainerAwareTrait');
		$trait->setContainer($container);

		$this->assertSame($container, $trait->getContainer());
	}

	/**
	 * @testdox getContainer() throws an ContainerNotFoundException, if no container is set
	 * @expectedException   \Joomla\DI\Exception\ContainerNotFoundException
	 */
	public function testGetContainerException()
	{
		$trait = $this->getObjectForTrait('\\Joomla\\DI\\ContainerAwareTrait');
		$trait->getContainer();
	}
}
