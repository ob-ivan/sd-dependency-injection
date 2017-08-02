<?php
namespace tests;

use SD\DependencyInjection\Container;

class LegacyService {
    private $container;
    private $name;

    public function __construct(string $name, Container $container) {
        $this->name = $name;
        $this->container = $container;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getContainer() {
        return $this->container;
    }
}
