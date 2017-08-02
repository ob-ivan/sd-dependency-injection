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

    public function connect(ProviderInterface $provider) {
        $this->initializers[$provider->getServiceName()] = function () use ($provider) {
            $this->inject($provider);
            return $provider->provide();
        };
    }

    public function register($name, $initializer) {
        if ($initializer instanceof Value) {
            $this->services[$name] = $initializer->getValue();
        } else {
            $this->initializers[$name] = $initializer;
        }
    }

    public function extend($name, $extender) {
        if (isset($this->services[$name])) {
            $this->services[$name] = $this->produce($extender);
        } elseif (isset($this->initializers[$name])) {
            $initializer = $this->initializers[$name];
            $this->initializers[$name] = function () use ($extender, $initializer, $name) {
                $this->services[$name] = $this->produce($initializer);
                return $this->inject($extender);
            };
        } else {
            throw new Exception("Cannot extend unknown service $name");
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
            $consumer = $this->injectDeclarer($consumer);
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
        $instance = $this->injectDeclarer($instance);
        return $instance;
    }

    private function injectDeclarer($object) {
        if ($object instanceof AutoDeclarerInterface) {
            $object = $this->injectByNames($object, $object->autoDeclareDependencies());
        }
        if ($object instanceof DeclarerInterface) {
            $object = $this->injectByNames($object, (array)$object->declareDependencies());
        }
        return $object;
    }

    private function injectByNames($object, array $names) {
        foreach ($names as $name) {
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
