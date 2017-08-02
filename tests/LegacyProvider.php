<?php
namespace tests;

use SD\DependencyInjection\AutoDeclarerInterface;
use SD\DependencyInjection\AutoDeclarerTrait;
use SD\DependencyInjection\ContainerAwareTrait;
use SD\DependencyInjection\DeclarerInterface;
use SD\DependencyInjection\ProviderInterface;

class LegacyProvider implements AutoDeclarerInterface, DeclarerInterface, ProviderInterface {
    use AutoDeclarerTrait;
    use ContainerAwareTrait;

    private $name;

    public function declareDependencies() {
        return ['name'];
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getServiceName(): string {
        return 'helloWorld';
    }

    public function provide() {
        return new LegacyService($this->name, $this->container);
    }
}
