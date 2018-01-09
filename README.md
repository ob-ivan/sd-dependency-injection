Registers services and injects them into consumers.

Installation
============

```bash
composer require ob-ivan/sd-dependency-injection
```

Usage
=====

Three types of dependency injection are supported:
- **Setter injection** aka **Interface injection** - given an Instance implementing DeclarerInterface
  call a setter for each declared dependency.
- **Constructor injection** - given a ClassName resolve its constructor arguments as dependency
  names and call constructor with appropriate services. Setter injection is invoked on the
  created instance.
- **Argument injection** - given a Function resolve its arguments as dependency names and call
  the Function with appropriate services.

You can use container to register services, or to produce consumers, or to inject dependencies
into arbitrary code.

- Consumer = Function | Instance
- ConsumerInitializer = ClassName | Consumer
- ServiceInitializer = ConsumerInitializer | Value

Construction
------------
Container is initialized with raw values, no Initalizers allowed:

```php
$container = new SD\DependencyInjection\Container([
    'name' => 'Chewbaka', // not interpreted as a class name
]);
```

You can set a name which will be used to refer to container itself:

```php
$container = new SD\DependencyInjection\Container([], 'container');
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

You can extend registered services:

```php
$container->register('currency', SD\Currency\Repository::class);
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
$controller = $container->produce(new HelloWorldController('Luke'));
$controller = $container->produce(function ($catchPhrase) {
    return new HelloWorldController($catchPhrase);
});
```

You can inject services into any Consumer:

```php
$serviceAwareCalculator = $container->inject($serviceUnawareCalculator);
$response = $container->inject(function ($helloWorldService) use ($name) {
    return $helloWorldService->greet($name);
});
```

Though discouraged, you can use container as service locator, if execution context does not let
you use the power of dependency injection:

```php
$legacyConsumer = new LegacyConsumer($container->get('brand_new_service'));
```

Development
===========
To run tests:

```bash
composer install
vendor/bin/phpunit
```
