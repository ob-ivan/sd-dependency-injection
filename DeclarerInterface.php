<?php

namespace SD\DependencyInjection;

interface DeclarerInterface {
    /**
     * @return string[]
    **/
    public function declareDependencies(): array;
}
