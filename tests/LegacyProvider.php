<?php
namespace tests;

use SD\DependencyInjection\AutoDeclareTrait;
use SD\DependencyInjection\ContainerAwareTrait;
use SD\DependencyInjection\DeclarerInterface;
use SD\DependencyInjection\ProviderInterface;

class LegacyProvider implements DeclarerInterface, ProviderInterface {
    use AutoDeclareTrait {
        declareDependencies as autoDeclareDependencies;
    }
    use ContainerAwareTrait;

    private $name;

    public function declareDependencies() {
        return array_merge($this->autoDeclareDependencies(), ['name']);
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return 'helloWorld';
    }

    public function provide() {
        return new LegacyService($this->name, $this->container);
    }
}
