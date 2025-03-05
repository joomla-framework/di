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
class LazyTest extends TestCase
{
    /**
     * @testdox  Creates lazy proxy
     *
     * @covers   Joomla\DI\Container
     * @uses     Joomla\DI\ContainerResource
     */
    public function testLazy(): void
    {
        $container = new Container();
        $container->set('stub1', fn() => new Stub1(), true, true);
        $container->set(
            'stub2',
            $container->lazy(Stub2::class, function($container) {
                return new Stub2($container->get('stub1'));
            }),
            true,
            true
        );

        $stub2 = $container->get('stub2');

        ob_start();
        var_dump($stub2);
        $type = ob_get_clean();

        $this->assertStringStartsWith('lazy proxy object', $type);

        $this->assertSame(
            $container->get('stub1'),
            $stub2->stub,
        );
    }
}
