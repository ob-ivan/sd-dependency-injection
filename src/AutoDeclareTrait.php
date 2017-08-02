<?php

namespace SD\DependencyInjection;

trait AutoDeclareTrait {
    private $prefixAutoDeclare = 'autoDeclare';

    /**
     * @return string[]
    **/
    public function declareDependencies() {
        $class = new \ReflectionClass($this);
        $deps = [];
        foreach ($class->getDefaultProperties () as $name => $value) {
            if (0 === strncmp($name, $this->prefixAutoDeclare, 11)) {
                $deps[] = $value;
            }
        }
        return $deps;
    }
}
