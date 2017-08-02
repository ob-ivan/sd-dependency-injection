<?php
namespace SD\DependencyInjection;

interface AutoDeclarerInterface {
    /**
     * @return string[]
    **/
    public function autoDeclareDependencies();
}
