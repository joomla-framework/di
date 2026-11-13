<?php

/**
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ContainerResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the resolution of autowired constructor arguments.
 */
#[CoversClass(Container::class)]
#[UsesClass(ContainerResource::class)]
class AutowiredArgumentsTest extends TestCase
{
    #[TestDox('Each instance of a non-shared autowired object receives its own non-shared dependencies')]
    public function testNonSharedDependenciesAreResolvedPerInstance()
    {
        $container = new Container();
        $first     = $container->buildObject(AutowiredConsumer::class);
        $second    = $container->get(AutowiredConsumer::class);

        $this->assertNotSame($first, $second);
        $this->assertNotSame($first->dependency, $second->dependency);
    }

    #[TestDox('Shared dependencies are still shared between instances of a non-shared autowired object')]
    public function testSharedDependenciesAreShared()
    {
        $container = new Container();
        $container->share(AutowiredDependency::class, static fn () => new AutowiredDependency());

        $first  = $container->buildObject(AutowiredConsumer::class);
        $second = $container->get(AutowiredConsumer::class);

        $this->assertSame($first->dependency, $second->dependency);
    }

    #[TestDox('A dependency redefined after building an object is used for new instances')]
    public function testRedefinedDependencyIsUsed()
    {
        $container = new Container();
        $container->buildObject(AutowiredConsumer::class);

        $replacement = new AutowiredDependency();
        $container->share(AutowiredDependency::class, $replacement);

        $this->assertSame($replacement, $container->get(AutowiredConsumer::class)->dependency);
    }

    #[TestDox('The dependencies of the first instance are resolved only once')]
    public function testFirstInstanceResolvesDependenciesOnce()
    {
        $container = new Container();
        $calls     = 0;

        $container->set(
            AutowiredDependency::class,
            static function () use (&$calls) {
                $calls++;

                return new AutowiredDependency();
            }
        );

        $container->buildObject(AutowiredConsumer::class);

        $this->assertSame(1, $calls);
    }
}

// @codingStandardsIgnoreStart

class AutowiredDependency
{
}

class AutowiredConsumer
{
    public $dependency;

    public function __construct(AutowiredDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}
