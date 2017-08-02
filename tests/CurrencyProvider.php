<?php
namespace tests;

use SD\DependencyInjection\DeclarerInterface;
use SD\DependencyInjection\ProviderInterface;

class CurrencyProvider implements DeclarerInterface, ProviderInterface {
    private $currencyStore;

    public function declareDependencies() {
        return ['currencyStore'];
    }

    public function setCurrencyStore($currencyStore) {
        $this->currencyStore = $currencyStore;
    }

    public function getServiceName(): string {
        return 'currency';
    }

    public function provide() {
        return new \stdClass;
    }
}
