<?php

namespace SD\DependencyInjection;

trait ContainerAwareTrait {
    private $container;

    public function setContainer(Container $container) {
        $this->container = $container;
    }
}
