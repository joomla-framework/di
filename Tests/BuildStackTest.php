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
 * Tests for the circular dependency detection in Container::buildObject().
 */
#[CoversClass(Container::class)]
#[UsesClass(ContainerResource::class)]
class BuildStackTest extends TestCase
{
    #[TestDox('A dependency that fails to build for a nullable argument does not cause a false circular dependency error later')]
    public function testFailedNestedBuildDoesNotLeak()
    {
        $container = new Container();

        $first = $container->buildObject(BuildStackOuter::class);
        $this->assertNull($first->inner);

        $second = $container->buildObject(BuildStackOuter::class);
        $this->assertInstanceOf(BuildStackOuter::class, $second);
        $this->assertNull($second->inner);
    }

    #[TestDox('A failed nested build in one container does not affect other containers')]
    public function testFailedNestedBuildDoesNotAffectOtherContainers()
    {
        (new Container())->buildObject(BuildStackOuter::class);

        $this->assertInstanceOf(BuildStackOuter::class, (new Container())->buildObject(BuildStackOuter::class));
    }

    #[TestDox('A failed build does not discard the outer frames of a running build')]
    public function testFailedBuildKeepsOuterFrames()
    {
        $container = new Container();
        $calls     = 0;

        $container->set(
            BuildStackCircular::class,
            static function (Container $c) use (&$calls) {
                if (++$calls > 3) {
                    throw new \LogicException('The circular dependency was not detected');
                }

                // A failed build in between must not make the container forget that BuildStackCircular is being built
                try {
                    $c->buildObject(BuildStackAbstract::class);
                } catch (DependencyResolutionException $e) {
                }

                return $c->buildObject(BuildStackCircular::class);
            }
        );

        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessage('Cannot resolve circular dependency for "' . BuildStackCircular::class . '"');

        $container->buildObject(BuildStackCircular::class);
    }
}

// @codingStandardsIgnoreStart

class BuildStackInner
{
    public function __construct(int $value)
    {
    }
}

class BuildStackOuter
{
    public $inner;

    public function __construct(?BuildStackInner $inner)
    {
        $this->inner = $inner;
    }
}

abstract class BuildStackAbstract
{
}

class BuildStackCircular
{
}
