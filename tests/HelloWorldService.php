<?php
namespace tests;

use SD\DependencyInjection\DeclarerInterface;
use SD\DependencyInjection\ContainerAwareTrait;

class HelloWorldService implements DeclarerInterface {
    use ContainerAwareTrait;

    private $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function declareDependencies() {
        return ['container'];
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getContainer() {
        return $this->container;
    }
}
