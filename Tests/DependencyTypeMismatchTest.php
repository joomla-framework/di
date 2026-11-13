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
 * Tests for autowiring an argument whose registered service has the wrong type.
 */
#[CoversClass(Container::class)]
#[UsesClass(ContainerResource::class)]
class DependencyTypeMismatchTest extends TestCase
{
    #[TestDox('A registered service of the wrong type is reported as such')]
    public function testWrongTypeIsReported()
    {
        $container = new Container();
        $container->set(TypeMismatchDependency::class, 'not an object');

        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not resolve the parameter "$dependency" of "%s::__construct()": The service for "%s" is of type "string", which does not match the parameter type.',
                TypeMismatchRequired::class,
                TypeMismatchDependency::class
            )
        );

        $container->buildObject(TypeMismatchRequired::class);
    }

    #[TestDox('A registered service of the wrong type falls back to the default value')]
    public function testWrongTypeFallsBackToDefault()
    {
        $container = new Container();
        $container->set(TypeMismatchDependency::class, static fn () => new \stdClass());

        $this->assertNull($container->buildObject(TypeMismatchOptional::class)->dependency);
    }
}

// @codingStandardsIgnoreStart

class TypeMismatchDependency
{
}

class TypeMismatchRequired
{
    public function __construct(TypeMismatchDependency $dependency)
    {
    }
}

class TypeMismatchOptional
{
    public $dependency;

    public function __construct(?TypeMismatchDependency $dependency = null)
    {
        $this->dependency = $dependency;
    }
}
