<?php
namespace tests;

class HelloWorldService implements SD\DependencyInjection\DeclarerInterface {
    use SD\DependencyInjection\ContainerAwareTrait;

    private $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function declareDependencies() {
        return ['container'];
    }

    public function getName() {
        return $this->name;
    }

    public function getContainer() {
        return $this->container;
    }
}
