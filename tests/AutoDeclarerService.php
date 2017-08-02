<?php
namespace tests;

use SD\DependencyInjection\AutoDeclarerTrait;
use SD\DependencyInjection\AutoDeclarerInterface;
use SD\DependencyInjection\ContainerAwareTrait;

class AutoDeclarerService implements AutoDeclarerInterface {
    use AutoDeclarerTrait;
    use ContainerAwareTrait;

    public function getContainer() {
        return $this->container;
    }
}
