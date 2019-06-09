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
 * Tests for Container class.
 */
class TaggedServiceTest extends TestCase
{
	/**
	 * @testdox A registered resource can be tagged
	 */
	public function testARegisteredResourceCanBeTagged()
	{
		$container = new class extends Container
		{
			public function getTags(): array
			{
				return $this->tags;
			}
		};

		$container->set(
			Stub6::class,
			function ()
			{
				return new Stub6;
			}
		);

		$container->tag('stub', [Stub6::class]);

		$this->assertSame($container->getTags()['stub'], [Stub6::class]);
	}

	/**
	 * @testdox All tagged services can be retrieved
	 */
	public function testAllTaggedServicesCanBeRetrieved()
	{
		$container = new Container;

		$container->set(
			Stub1::class,
			function ()
			{
				return new Stub1;
			}
		);

		$container->set(
			Stub2::class,
			function (Container $container)
			{
				return new Stub2($container->get(Stub1::class));
			}
		);

		$container->set(
			Stub4::class,
			function (Container $container)
			{
				return new Stub4($container->get(Stub2::class));
			}
		);

		$container->tag('stub', [Stub1::class, Stub2::class, Stub4::class]);

		$services = $container->getTagged('stub');

		$this->assertCount(3, $container->getTagged('stub'));
	}
}
