<?php

/**
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ContainerAwareTrait;
use Joomla\DI\Exception\ContainerNotFoundException;
use Joomla\DI\Tests\Stubs\ContainerAwareTraitObject;
use Joomla\Test\TestHelper;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ContainerAwareTrait class.
 */
#[CoversTrait(ContainerAwareTrait::class)]
#[UsesClass(Container::class)]
class ContainerAwareTraitTest extends TestCase
{
    /**
     * @var ContainerAwareTrait
     */
    protected $object;

    #[TestDox('Container can be set with setContainer()')]
    public function testGetContainer()
    {
        $container = new Container();

        $object = new ContainerAwareTraitObject();
        $object->setContainer($container);

        $this->assertSame($container, TestHelper::getValue($object, 'container'));
    }

    #[TestDox('getContainer() throws an ContainerNotFoundException, if no container is set')]
    public function testGetContainerException()
    {
        $this->expectException(ContainerNotFoundException::class);

        $object = new ContainerAwareTraitObject();

        TestHelper::invoke($object, 'getContainer');
    }
}
