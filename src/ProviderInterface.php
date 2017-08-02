<?php
namespace SD\DependencyInjection;

interface ProviderInterface {
    public function getServiceName(): string;
    public function provide();
}
