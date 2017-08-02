<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SD\DependencyInjection\AutoDeclareTrait;
use SD\DependencyInjection\Container;

class AutoDeclareTraitTest extends TestCase {
    public function testDeclareDependencies() {
        $container = new Container([], 'container');
        $service = $container->produce(AutoDeclareService::class);
        $this->assertEquals($container, $service->getContainer(), 'Must inject container with auto declare');
    }
}
