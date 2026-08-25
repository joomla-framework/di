<?php

/**
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Container class.
 */
#[CoversClass(Container::class)]
class ServiceProviderTest extends TestCase
{
    #[TestDox('When registering a service provider, its register() method is called with the container instance')]
    public function testRegisterServiceProvider()
    {
        $container = new Container();

        /** @var ServiceProviderInterface|MockObject $mock */
        $mock = $this->createMock(ServiceProviderInterface::class);

        $mock->expects($this->once())
            ->method('register')
            ->with($container);

        $container->registerServiceProvider($mock);
    }
}
