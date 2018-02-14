<?php
namespace SD\DependencyInjection;

trait ContainerAwareTrait
{
    protected $autoDeclareContainer = 'container';
    private $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }
}
