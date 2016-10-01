# Container

[![Build Status](https://travis-ci.org/ecfectus/container.svg?branch=master)](https://travis-ci.org/ecfectus/container)

Lean PSR11 (draft) compatible dependency injection container.

Inspired by both illuminate/container and league/container this container has a few advantages.

## Low memory footprint

This is a very lazy container, binding services into it simply stores to its internal definition (which is an array, not an object).

No objects are created, no parameters are instantiated, it simply stores the callback.

Alias callbacks are only called if/when the alias is requested by the ```::get($alias)``` method.

## Callback injection without reflection

illuminate/container offers up the entire container instance to callbacks, while league/container returns a definition were you can add arguments in a fluent manner.

The problem with this is either you are passing an entire container object to a callback that may not need much from it, consuming memory.

Or you are creating additional objects to store definitions, again consuming memory.

The solution is inspired by laravels routing component, provide an array as the callback:

```php
//this is just registering a normal callback which can optionally accept the container instance.
$container->bind(SomeClassInterface::class, function($container){//optionally get the passed in container, this is default
    return new SomeImplementation();
});


//this method allows you to define the whole dependency list for the callback
//by passing an array with the callback as the first item, and what needs to be passed to it as the rest of the array
//this allows for saving in both speed and memory usage.
$container->bind(SomeClassInterface::class, [function(A $a, B $b){
    return new SomeImplementation();
}, A::class, B::class]);
```

We have found even testing with as little as 5 dependant classes this method of providing the dependency list to be TWICE as fast and has no reflection needs.

## Usage

The container conforms the current PSR11 container spec (in draft), so basic usage is as follows:

```php
$container = new Ecfectus\Container\Container();
//optionally include a reflection based delegate (only used if no registration exists, this is required if you plan on using the service provider container as well)
$container->delegate(new Ecfectus\Container\ReflectionContainer());

//optionally include the service provider container
$serviceProviderContainer = new ServiceProviderContainer();
$container->delegate($serviceProviderContainer);

//if registered add service providers either on the delegated container OR on the main container via the use of magic methods
$serviceProviderContainer->addServiceProvider(\Name\Of\ServiceProvider::class);//must extend the \Ecfectus\Container\ServiceProvider\AbstractServiceProvider class

//psr interface methods

$container->get('id');//returns the instance, or throws a NotFoundException

$container->has('id');//returns bool

//methods specific to this container

//bind a service
$container->bind($id, $callbackOrArray, $share = false);

//share a service (same as above with third flag as true)
$container->share($id, $callbackOrArray);

//$callbackOrArray can be any of the following:

//classname
$container->bind($id, \Some\Class::class);

//instance
$container->bind($id, new \Some\Class());

//callable
$container->bind($id, function($container){
    return new \Some\Class();
});

//an array containing a callable and the required arguments, to save on using reflection (better performance)
$container->bind($id, [function($arg, \Some\Class $instance){
    return $instance->with($arg);
}, 'argument value', \Some\Class::class]);

//allows you to alter the instance before returning it from the container, you must return the instance
$container->extend($id, function($instance, $container){
    return $instance;
});
```
If using services providers they simply need to provide an array of the services they define, and the register function creating the bindings.

```php
<?php
use Ecfectus\Container\ServiceProvider\AbstractServiceProvider;

class SomeServiceProvider extends AbstractServiceProvider
{

    protected $provides = [
      someClass::class
    ];

    public function register(){
            /**
             * Or use the shortcuts $this->bind() and $this->share()
             */
        $this->getContainer()->bind(someClass::class, function(){
            return new someClass();
        });
    }

}
```

Service providers can also implement the ```BootableServiceProviderInterface``` and provide a ```::boot()``` method which will get called when the service provider
is added via the ```addServiceProvider``` function if the container has already been booted, or when the ```$container->bootServiceProviders();``` method is called.
This function also has access to the container.

### Notes

There has been a very primitive benchmark done between this as the league container (which is faster than illuminate).

It looks positive. Around 20% faster and generally uses less memory (up to 70% less).

The reflection container give you complete freedom, but this comes at a cost (can be 2+ times slower), where possible provide the dependency list and register all
objects in the container to ensure best performance.
