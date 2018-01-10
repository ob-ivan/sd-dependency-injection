Registers services and injects them into consumers.

Installation
============

```bash
composer require ob-ivan/sd-dependency-injection
```

Usage
=====

Terminology
-----------
In large applications a situation when some class instances are to be shared among many other classes
is common. Think of a database connection instance shared among various controller classes.

Those shared classes are called _services_ (they provide some useful functionality to others),
and those sharing are called _consumers_ (they consume the services provided).

The process of letting a consumer know all services it requires access to is called _injection_.

A consumer may declare formally a list of services it requires. This is called _listing_ its
_dependencies_. The process of taking a consumer's dependency list and injecting corresponging
services into it is called, unsurprisingly, _dependency injection_.

A _dependency injection container_ (DIC) is a way to implement dependency injection. It requires
that each service is _registered_ within the container under a certain _common name_, and that
consumers list common names of their required services as formal dependency lists.

Dependecies may be injected into consumers in several different ways. In this library, these
three types of dependency injection are supported:
- **Setter injection** aka **Interface injection** - given a consumer Instance implementing
    DeclarerInterface call a setter for each declared dependency.
- **Constructor injection** - given a consumer's ClassName resolve its constructor arguments as
    dependency names and call constructor with appropriate services. Setter injection is then invoked
    on the created instance.
- **Argument injection** - given a consumer Function resolve its arguments as dependency names and
    call the Function with appropriate services.

You can use a container to register services, or to produce consumers, or to inject dependencies
into arbitrary code.

- Consumer = Function | Instance
- ConsumerInitializer = ClassName | Consumer
- ServiceInitializer = ConsumerInitializer | Value

Construction
------------
A container is initialized with raw values, no Initalizers allowed:

```php
$container = new SD\DependencyInjection\Container([
    'name' => 'Chewbaka', // not interpreted as a class name
]);
```

If you have several containers, you can create a new one by merging them:

```php
$mergedContainer = SD\DependencyInjection\Container::merge($container1, $container2);
```

Registering services
--------------------
Services are registered with ServiceInitializers:

```php
// Setter injection - use when constructing a Service is cheap and doesn't involve resource allocating.
$container->register('helloWorld', new HelloWorldService('Anakin'));

// Constructor injection + Setter injection - use when Service does not require constructor arguments.
$container->register('helloWorld', HelloWorldService::class);

// Argument injection + Setter injection - use when Service requires a constructor argument or
// Service construction is anyhow a complicated process.
$container->register('helloWorld', function ($name) {
    if ($name === strtoupper($name)) {
        return new HelloShouterService($name);
    }
    return new HelloWorldService($name);
});

// No injection - use for setting parameters.
$container->register('name', $container->value('Skywalker'));
```

You do not have to register container within itself, as it is self-aware by default:

```php
// The 'container' common name is reserved to refer to the container itself.
$container === $container->get('container');
```

You can extend registered services:

```php
// Initial registering:
$container->register('currency', SD\Currency\Repository::class);

// Later on:
$container->extend('currency', function ($container, $currency) {
    $store = $container->produce(SD\CurrencyStore\Wpdb::class);
    $currency->setStore($store);
    return $currency;
});
```

Consumer production
-------------------
Consumers are produced with ConsumerInitializers (Value is not supported):

```php
$controller = $container->produce(HelloWorldController::class);
// or:
$controller = $container->produce(new HelloWorldController('Luke'));
// or:
$controller = $container->produce(function ($catchPhrase) {
    return new HelloWorldController($catchPhrase);
});
```

You can inject services into any Consumer:

```php
// Inject into an already instantiated consumer:
$serviceAwareCalculator = $container->inject($serviceUnawareCalculator);

// Inject into a callable:
$response = $container->inject(function ($helloWorldService) use ($name) {
    return $helloWorldService->greet($name);
});
```

Though discouraged, you can use container as service locator, if execution context does not let
you use the power of dependency injection:

```php
$legacyConsumer = new LegacyConsumer($container->get('brand_new_service'));
```

Defining a consumer
-------------------
Class consumers (as opposed to callable consumers) declare their dependencies in any of following
ways --- or a combination of both:

- By implementing `DeclarerInterface` and returning a list of common names from `declareDependencies`
method.
- By implementing `AutoDeclarerInterface` and importing `AutoDeclarerTrait` and `AwareTrait`s
of their respective dependencies.

As listing common names in `declareDependencies` method causes heavy reduplication of common names,
we recommend to define an `AwareTrait` for each service you define.

Here's a sample code:

```php
trait ExampleAwareTrait {
    // Here's the magic. Create a field named starting with $autoDeclare and containing the service's
    // common name as a value. Don't forget to import AutoDeclarerTrait!
    private $autoDeclareExample = 'example';
    private $example;

    // Setter name must match the common name.
    public function setExample(ExampleService $example) {
        $this->example = $example;
    }
}
```

This way defining a consumer in a following way will tell a container to inject ExampleService
into it --- or to throw an exception if it's not available.

```php
use SD\DependencyInjection\AutoDeclarerInterface;
use SD\DependencyInjection\AutoDeclarerTrait;

class ExampleConsumer implements AutoDeclarerInterface {
    use AutoDeclarerTrait;
    use ExampleAwareTrait;

    // An example way to access an example service instance.
    public function getExample() {
        return $this->example;
    }
}
```

Service providers
-----------------
You can encapsulate a service's common name further by putting it into a provider.

```php
use SD\DependencyInjection\ProviderInterface;

// This provides a correspondence between the service instance and its common name.
class ExampleProvider implements ProviderInterface {
    public function getServiceName(): string {
        return 'example'; // the common name
    }

    public function provide() {
        return new ExampleService();
    }
}
```

Then the service is registered with its provider's instance:

```php
$container->connect(new ExampleProvider());
```

This way neither registration code nor consumers need to know the common name. They just refer to
Provider instance and the AwareTrait, and it just works. Hey, it's magic!

Development
===========
To run tests:

```bash
composer install
vendor/bin/phpunit
```
