<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SD\DependencyInjection\Container;

class ContainerAwareTraitTest extends TestCase
{
    public function testInheritAutoDeclare()
    {
        $container = new Container();
        $subclassConsumer = $container->produce(SubclassConsumer::class);
        $this->assertSame($container, $subclassConsumer->getService(), 'Subclass must inherit auto declare trait');
    }
}
