<?php
namespace tests;

use SD\DependencyInjection\AutoDeclareTrait;
use SD\DependencyInjection\DeclarerInterface;
use SD\DependencyInjection\ContainerAwareTrait;

class AutoDeclareService implements DeclarerInterface {
    use AutoDeclareTrait;
    use ContainerAwareTrait;

    public function getContainer() {
        return $this->container;
    }
}
