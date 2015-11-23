<?php

namespace Conformity\Container\Test;

use Conformity\Container\Container;
use Conformity\Container\ReflectionContainer;
use Conformity\Container\ServiceProviderContainer;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Asserts that the container can set and get a simple closure with args.
     */
    public function testSetsAndGetsSimplePrototypedClosure()
    {
        $container = new Container();

        $container->bind('test', [function ($arg) {
            return $arg;
        }, 'hello world']);

        $this->assertTrue($container->has('test'));

        $this->assertEquals($container->get('test'), 'hello world');
    }

    /**
     * Asserts that the container sets and gets an instance as shared.
     */
    public function testSetsAndGetInstanceAsShared()
    {
        $container = new Container;

        $class = new \stdClass;

        $container->bind('test', $class);

        $this->assertTrue($container->has('test'));

        $this->assertSame($container->get('test'), $class);
    }

    /**
     * Asserts that an exception is thrown when attempting to get service that
     * does not exist.
     */
    public function testThrowsWhenGettingUnmanagedService()
    {
        $this->setExpectedException('InvalidArgumentException');

        $container = new Container;

        $container->get('nothing');
    }

    /**
     * Asserts that fetching a shared item always returns the same item.
     */
    public function testGetSharedItemReturnsTheSameItem()
    {
        $alias = 'foo';

        $container = new Container;

        $container->share($alias, function () {
            return new \stdClass;
        });

        $item = $container->get($alias);

        $this->assertSame($item, $container->get($alias));
    }

    /**
     * Asserts that asking container for an item that has a shared definition returns true.
     */
    public function testHasReturnsTrueForSharedDefinition()
    {
        $alias = 'foo';

        $container = new Container;

        $container->share($alias, function () {
            return new \stdClass;
        });

        $this->assertTrue($container->has($alias));
    }

    /**
     * Asserts that an exception is thrown when the extend method cannot find a definition to extend.
     * @expectedException \Conformity\Container\NotFoundException
     */
    public function testExtendThrowsWhenCannotFindDefinition()
    {
        $container = new Container;

        $container->extend('something', function () {});
    }

    /**
     * Asserts that providing no concrete results in the id being used as the concrete should it be a class.
     */
    public function testProvidingNoConcreteResultsInAlias()
    {
        $container = new Container;

        $container->bind(\stdClass::class);

        $this->assertTrue($container->has(\stdClass::class));

        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
    }

    /**
     * Asserts that providing arguments to the closure results in the object being passed them.
     */
    public function testProvidingArgumentsToBinding()
    {
        $container = new Container;

        $container->bind('test', [function ($arg) {return $arg;}, 'argument1']);

        $this->assertEquals('argument1', $container->get('test'));
    }

    /**
     * Asserts that providing class arguments to the closure results in the object being passed them.
     */
    public function testProvidingClassArgumentsToBinding()
    {
        $container = new Container;
        $container->delegate(new ReflectionContainer());

        $container->bind('test', [function (\stdClass $arg) {return $arg;}, \stdClass::class]);

        $this->assertInstanceOf('stdClass', $container->get('test'));
    }

    /**
     * Asserts that using the reflection container results in a class being resolved that hasn't been registered.
     */
    public function testRequestingNoneBoundClassResultsInReflectionCall()
    {
        $container = new Container;
        $container->delegate(new ReflectionContainer());

        $resolved = $container->get(TestClassHasDependencies::class);

        $this->assertInstanceOf(TestClassHasDependencies::class, $resolved);

        $this->assertInstanceOf('stdClass', $resolved->argument);
    }

    /**
     * Assert delegation resolves instances
     */
    public function testReflectionDelegationResultsInInstanceBeingCreated()
    {
        $container = new Container();

        $this->assertFalse($container->has(\stdClass::class));

        $container->delegate(new ReflectionContainer());

        $this->assertTrue($container->has(\stdClass::class));

        $this->assertInstanceOf('stdClass', $container->get(\stdClass::class));
    }

    /**
     * Assert delegation resolves instances via service providers
     */
    public function testServiceProviderDelegationResultsInInstanceBeingCreated()
    {
        $container = new Container();

        $container->delegate(new ReflectionContainer());

        $serviceProvider = new ServiceProviderContainer();

        $container->delegate($serviceProvider);

        $serviceProvider->addServiceProvider(TestServiceProvider::class);

        $this->assertTrue($container->has(\stdClass::class));

        $this->assertInstanceOf('stdClass', $container->get(\stdClass::class));
    }
}
