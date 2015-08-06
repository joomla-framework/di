<?php
/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\Resource;

/**
 * Tests for Resource class.
 */
class ResourceTest extends \PHPUnit_Framework_TestCase
{
	public function dataInstantiation()
	{
		return array(
			'shared, protected'         => array(
				'mode' => Resource::SHARE | Resource::PROTECT,
				'shared'    => true,
				'protected' => true
			),
			'shared, not protected (explicit)'     => array(
				'mode' => Resource::SHARE | Resource::NO_PROTECT,
				'shared'    => true,
				'protected' => false
			),
			'not shared, protected (explicit)'     => array(
				'mode' => Resource::NO_SHARE | Resource::PROTECT,
				'shared'    => false,
				'protected' => true
			),
			'not shared, not protected (explicit)' => array(
				'mode' => Resource::NO_SHARE | Resource::NO_PROTECT,
				'shared'    => false,
				'protected' => false
			),
			'shared, not protected (implicit)'     => array(
				'mode'      => Resource::SHARE,
				'shared'    => true,
				'protected' => false
			),
			'not shared, protected (implicit)'     => array(
				'mode'      => Resource::PROTECT,
				'shared'    => false,
				'protected' => true
			),
			'not shared, not protected (implicit)' => array(
				'mode'      => null,
				'shared'    => false,
				'protected' => false
			),
		);
	}

	/**
	 * @testdox The resource descriptor supports 'shared' and 'protected' modes, defaulting to 'not shared' and 'not protected'
	 * @dataProvider dataInstantiation
	 */
	public function testInstantiation($mode, $shared, $protected)
	{
		$container = new Container();

		if ($mode === null)
		{
			$descriptor = new Resource($container, 'dummy');
		}
		else
		{
			$descriptor = new Resource($container, 'dummy', $mode);
		}

		$this->assertEquals($shared, $descriptor->isShared());
		$this->assertEquals($protected, $descriptor->isProtected());
	}

	/*
	 * If a factory was provided, the resource is created and - if it is a shared resource - cached internally.
	 * If the resource was provided directly, that resource is returned.
	 */
}
