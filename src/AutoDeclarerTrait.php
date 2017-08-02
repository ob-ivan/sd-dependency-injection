<?php

namespace SD\DependencyInjection;

trait AutoDeclarerTrait {
    private $prefixAutoDeclare = 'autoDeclare';

    /**
     * @return string[]
    **/
    public function autoDeclareDependencies(): array {
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
