<?php
namespace tests;

use SD\DependencyInjection\AutoDeclarerInterface;
use SD\DependencyInjection\AutoDeclarerTrait;

class CurrencyStore implements AutoDeclarerInterface {
    use AutoDeclarerTrait;

    private $autoDeclareCurrency = 'currency';
    private $currency;

    public function setCurrency($currency) {
        $this->currency = $currency;
    }
}
