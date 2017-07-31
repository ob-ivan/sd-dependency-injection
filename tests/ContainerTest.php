<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SD\DependencyInjection\Container;
use SD\DependencyInjection\Exception;

class ContainerTest extends TestCase {
    public function testRegisterClassName() {
        $name = 'Padme';
        $container = new Container(
            [
                'name' => $name,
            ],
            'container'
        );
        $serviceName = 'hello_world';
        $className = HelloWorldService::class;
        $container->register($serviceName, $className);
        $service = $container->get($serviceName);
        $this->assertInstanceOf($className, $service, 'Must return instance of the given class');
        $this->assertEquals($name, $service->getName(), 'Must inject name from config');
        $this->assertEquals($container, $service->getContainer(), 'Must inject container by setter');
    }

    public function testDetectCyclicDependencies() {
        $container = new Container();
        $container->register('a', function ($b) { return 1; });
        $container->register('b', function ($a) { return 2; });
        $this->expectException(Exception::class);
        $container->get('a');
    }
}
