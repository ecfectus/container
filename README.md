# container

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

## Usage

The container conforms the current PSR11 container spec (in draft), so basic usage is as follows:

```php
$container = new LeeMason\Container\Container();
//optionally include a reflection based delegate (only used if no registration exists)
$container->delegate(new LeeMason\Container\ReflectionContainer());

//psr interface methods

$container->get('id');//returns the instance, or throws a NotFoundException

$container->has('id');//returns bool

//methods sepcific to this container

//bind a service
$container->bind($id, $callbackOrArray, $share = false);

//share a service (same as above with third flag as true
$container->share($id, $callbackOrArray);

//allows you to alter the instance before returning it from the container, you must return the instance
$container->extend($id, function($instance, $container){
    return $instance;
});
```
