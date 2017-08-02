<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SD\DependencyInjection\AutoDeclarerTrait;
use SD\DependencyInjection\Container;

class AutoDeclarerTraitTest extends TestCase {
    public function testDeclareDependencies() {
        $container = new Container([], 'container');
        $service = $container->produce(AutoDeclarerService::class);
        $this->assertEquals($container, $service->getContainer(), 'Must inject container with auto declare');
    }
}
