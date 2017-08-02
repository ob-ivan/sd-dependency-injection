<?php
namespace SD\DependencyInjection;

interface ProviderInterface {
    public function getName(): string;
    public function provide();
}
