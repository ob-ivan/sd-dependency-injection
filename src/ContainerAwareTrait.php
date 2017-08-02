<?php

namespace SD\DependencyInjection;

trait ContainerAwareTrait {
    private $autoDeclareContainer = 'container';
    private $container;

    public function setContainer(Container $container) {
        $this->container = $container;
    }
}
