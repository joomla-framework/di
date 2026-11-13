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
 * Tests for autowiring constructor arguments with union and intersection types.
 */
#[CoversClass(Container::class)]
#[UsesClass(ContainerResource::class)]
class CompoundTypeTest extends TestCase
{
    #[TestDox('A required union typed argument cannot be autowired')]
    public function testRequiredUnionTypeThrowsAnException()
    {
        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessage('Union and intersection typehints are not supported.');

        (new Container())->buildObject(CompoundTypeUnion::class);
    }

    #[TestDox('A required intersection typed argument cannot be autowired')]
    public function testRequiredIntersectionTypeThrowsAnException()
    {
        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessage('Union and intersection typehints are not supported.');

        (new Container())->buildObject(CompoundTypeIntersection::class);
    }

    #[TestDox('A nullable union typed argument receives null')]
    public function testNullableUnionTypeReceivesNull()
    {
        $this->assertNull((new Container())->buildObject(CompoundTypeNullableUnion::class)->stub);
    }

    #[TestDox('A nullable intersection typed argument receives null')]
    public function testNullableIntersectionTypeReceivesNull()
    {
        $this->assertNull((new Container())->buildObject(CompoundTypeNullableIntersection::class)->stub);
    }

    #[TestDox('A class with a required intersection typed argument is passed null for a nullable dependency')]
    public function testIntersectionTypeInNullableDependency()
    {
        $this->assertNull((new Container())->buildObject(CompoundTypeIntersectionConsumer::class)->stub);
    }
}

// @codingStandardsIgnoreStart

interface CompoundTypeFirst
{
}

interface CompoundTypeSecond
{
}

class CompoundTypeUnion
{
    public function __construct(CompoundTypeFirst|CompoundTypeSecond $stub)
    {
    }
}

class CompoundTypeIntersection
{
    public function __construct(CompoundTypeFirst&CompoundTypeSecond $stub)
    {
    }
}

class CompoundTypeNullableUnion
{
    public $stub;

    public function __construct(CompoundTypeFirst|CompoundTypeSecond|null $stub)
    {
        $this->stub = $stub;
    }
}

class CompoundTypeNullableIntersection
{
    public $stub;

    public function __construct((CompoundTypeFirst&CompoundTypeSecond)|null $stub)
    {
        $this->stub = $stub;
    }
}

class CompoundTypeIntersectionConsumer
{
    public $stub;

    public function __construct(?CompoundTypeIntersection $stub)
    {
        $this->stub = $stub;
    }
}
