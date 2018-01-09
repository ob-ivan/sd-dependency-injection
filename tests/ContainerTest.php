<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SD\DependencyInjection\Container;
use SD\DependencyInjection\Exception;

class ContainerTest extends TestCase {
    public function testRegisterValue() {
        $container = new Container();
        $name = 'name';
        $value = new \stdClass();
        $container->register($name, $container->value($value));
        $result = $container->get($name);
        $this->assertEquals($value, $result, 'Must return wrapped value as is');
    }

    public function testRegisterClassName() {
        $name = 'Padme';
        $container = new Container([
            'name' => $name,
        ]);
        $serviceName = 'helloWorld';
        $className = HelloWorldService::class;
        $container->register($serviceName, $className);
        $service = $container->get($serviceName);
        $this->assertInstanceOf($className, $service, 'Must return instance of the given class');
        $this->assertEquals($name, $service->getName(), 'Must inject name from config');
        $this->assertEquals($container, $service->getContainer(), 'Must inject container by setter');
    }

    public function testDetectCyclicDependenciesSimple() {
        $container = new Container();
        $container->register('a', function ($b) { return 1; });
        $container->register('b', function ($a) { return 2; });
        $this->expectException(Exception::class);
        $container->get('a');
    }

    public function testDetectCyclicDependenciesComplex() {
        $container = new Container();
        $container->register('currencyStore', CurrencyStore::class);
        $container->connect(new CurrencyProvider());
        $this->expectException(Exception::class);
        $container->get('currency');
    }

    public function testExtend() {
        $name1 = 'Jar Jar Binks';
        $name2 = 'Palpatine';
        $container = new Container([
            'name' => $name1,
        ]);
        $serviceName = 'helloWorld';
        $container->register($serviceName, HelloWorldService::class);
        $container->extend($serviceName, function ($helloWorld) use ($name2) {
            $helloWorld->setName($name2);
            return $helloWorld;
        });
        $service = $container->get($serviceName);
        $this->assertEquals($name2, $service->getName(), 'Must return modified name');
    }

    public function testConnect() {
        $name = 'Luke Skywalker';
        $container = new Container([
            'name' => $name,
        ]);
        $provider = new LegacyProvider();
        $container->connect($provider);
        $service = $container->get($provider->getServiceName());
        $this->assertInstanceOf(LegacyService::class, $service, 'Must return instance of LegacyService');
        $this->assertEquals($name, $service->getName(), 'Must inject name from config');
        $this->assertEquals($container, $service->getContainer(), 'Must inject container by setter');
    }

    public function testMerge() {
        $post = new \stdClass();
        $request = new \stdClass();
        $postContainer = new Container([
            'post' => $post,
        ]);
        $requestContainer = new Container([
            'request' => $request,
        ]);
        $mergedContainer = Container::merge($postContainer, $requestContainer);
        $consumer = $mergedContainer->inject(new MultiConsumer());
        $this->assertSame($post, $consumer->getPost(), 'Must inject post from post container');
        $this->assertSame($request, $consumer->getRequest(), 'Must inject request from request container');
        $this->assertSame($mergedContainer, $consumer->getContainer(), 'Must inject merged container');
    }
}
