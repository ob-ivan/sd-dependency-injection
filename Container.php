<?php

namespace SD\DependencyInjection;

class Container {
    private $initializers = [];
    private $services = [];

    public function __construct(array $config = [], $selfName = '') {
        $this->services = $config;
        if ($selfName) {
            $this->services[$selfName] = $this;
        }
    }

    public function register($name, $initializer) {
        if ($initializer instanceof Value) {
            $this->services[$name] = $initializer->getValue();
        } else {
            $this->initializers[$name] = $initializer;
        }
    }

    public function value($value) {
        return new Value($value);
    }

    public function produce($initializer) {
        return $this->produceRecursive($initializer, []);
    }

    public function inject($consumer) {
        if (is_callable($consumer)) {
            $parameters = $this->getParameterValues(new \ReflectionFunction($consumer));
            return $consumer(...$parameters);
        } else {
            if ($consumer instanceof DeclarerInterface) {
                $consumer = $this->injectDeclared($consumer);
            }
            return $consumer;
        }
    }

    // Public for compatibility mode only.
    public function get($name) {
        return $this->getRecursive($name, []);
    }

    private function produceRecursive($initializer, array $names) {
        if (is_string($initializer)) {
            $class = new \ReflectionClass($initializer);
            $constructor = $class->getConstructor();
            if ($constructor) {
                $parameters = $this->getParameterValues($constructor, $names);
                $instance = new $initializer(...$parameters);
            } else {
                $instance = new $initializer();
            }
        } elseif (is_callable($initializer)) {
            $parameters = $this->getParameterValues(new \ReflectionFunction($initializer), $names);
            $instance = $initializer(...$parameters);
        } else {
            $instance = $initializer;
        }
        if ($instance instanceof DeclarerInterface) {
            $instance = $this->injectDeclared($instance);
        }
        return $instance;
    }

    private function injectDeclared(DeclarerInterface $object) {
        foreach ($object->declareDependencies() as $name) {
            $setter = 'set' . implode('', array_map('ucfirst', explode('_', $name)));
            if (!method_exists($object, $setter)) {
                throw new Exception("Object declared $name dependency, but setter method $setter was not found");
            }
            $object->$setter($this->get($name));
        }
        return $object;
    }

    /**
     * Return an initialized service.
     *
     *  @param $name string
     *  @param $names string[] Already initialized names, used to detect cyclic dependencies.
     *  @return mixed
    **/
    private function getRecursive(string $name, array $names) {
        if (!isset($this->services[$name])) {
            if (in_array($name, $names)) {
                throw new Exception("Cyclic dependency found while resolving $name");
            }
            $names[] = $name;
            if (!isset($this->initializers[$name])) {
                throw new Exception("No initializer for $name");
            }
            $this->services[$name] = $this->produceRecursive($this->initializers[$name], $names);
        }
        return $this->services[$name];
    }

    private function getParameterValues(\ReflectionFunctionAbstract $function, array $names = []) {
        return array_map(
            function ($parameter) use ($names) {
                return $this->getRecursive($parameter->getName(), $names);
            },
            $function->getParameters()
        );
    }
}
