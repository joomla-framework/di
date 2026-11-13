<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ContainerResource;
use Joomla\DI\Exception\DependencyResolutionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the circular dependency detection between factories.
 */
#[CoversClass(Container::class)]
#[UsesClass(ContainerResource::class)]
class FactoryCycleTest extends TestCase
{
    #[TestDox('Factories that depend on each other throw a DependencyResolutionException')]
    public function testFactoryCycleIsDetected()
    {
        $calls     = 0;
        $container = new Container();
        $container->set('a', static fn (Container $c) => $c->get('b'));
        $container->set(
            'b',
            static function (Container $c) use (&$calls) {
                if (++$calls > 5) {
                    throw new \LogicException('The circular dependency was not detected');
                }

                return $c->get('a');
            }
        );

        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessage('Cannot resolve circular dependency for "a"');

        $container->get('a');
    }

    #[TestDox('The container can be used again after a circular dependency was detected')]
    public function testContainerIsUsableAfterFactoryCycle()
    {
        $container = new Container();
        $container->set('a', static fn (Container $c) => $c->get('b'));
        $container->set('b', static fn (Container $c) => $c->get('a'));

        try {
            $container->get('a');
        } catch (DependencyResolutionException $e) {
        }

        $container->set('b', 'value');

        $this->assertSame('value', $container->get('a'));
    }

    #[TestDox('A factory can get the same service several times from other factories')]
    public function testRepeatedDependencyIsNoCycle()
    {
        $container = new Container();
        $container->set('dependency', 'value');
        $container->set('first', static fn (Container $c) => $c->get('dependency'));
        $container->set('second', static fn (Container $c) => [$c->get('first'), $c->get('dependency'), $c->get('first')]);

        $this->assertSame(['value', 'value', 'value'], $container->get('second'));
    }
}
