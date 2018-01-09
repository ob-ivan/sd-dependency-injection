<?php
namespace SD\DependencyInjection;

class Container {
    const SELF_NAME = 'container';

    private $initializers = [];
    private $services = [];

    /**
     *  @var $usedNames string[] Already initialized names, used to detect cyclic dependencies.
    **/
    private $usedNames = [];

    /**
     * Instantiate a container with a starting set of services.
     *
     *  @param  [string $name => mixed $service] $config
     *  @param  string $selfName is DEPRECATED
    **/
    public function __construct(array $config = [], $selfName = '') {
        $this->services = $config;
        if ($selfName) {
            trigger_error('selfName parameter is deprecated, "' . self::SELF_NAME . '" will be used instead', E_USER_DEPRECATED);
            $this->services[$selfName] = $this;
        }
    }

    public function connect(ProviderInterface $provider) {
        $this->register($provider->getServiceName(), function () use ($provider) {
            $this->injectRecursive($provider);
            return $provider->provide();
        });
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
        $this->usedNames = [];
        return $this->produceRecursive($initializer);
    }

    public function inject($consumer) {
        $this->usedNames = [];
        return $this->injectRecursive($consumer);
    }

    public static function merge(self ...$containers): self {
        $merged = new self();
        foreach ($containers as $container) {
            foreach ($container->initializers as $name => $initializer) {
                $merged->initializers[$name] = $initializer;
            }
            foreach ($container->services as $name => $service) {
                $merged->services[$name] = $service;
            }
        }
        return $merged;
    }

    // Public for compatibility mode only.
    public function get($name) {
        $this->usedNames = [];
        return $this->getRecursive($name);
    }

    private function injectRecursive($consumer) {
        if (is_callable($consumer)) {
            $parameters = $this->getParameterValues(new \ReflectionFunction($consumer));
            return $consumer(...$parameters);
        } else {
            $consumer = $this->injectDeclarer($consumer);
            return $consumer;
        }
    }

    private function produceRecursive($initializer) {
        if (is_string($initializer)) {
            $class = new \ReflectionClass($initializer);
            $constructor = $class->getConstructor();
            if ($constructor) {
                $parameters = $this->getParameterValues($constructor);
                $instance = new $initializer(...$parameters);
            } else {
                $instance = new $initializer();
            }
        } elseif (is_callable($initializer)) {
            $parameters = $this->getParameterValues(new \ReflectionFunction($initializer));
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

    private function injectByNames($object, array $declaredNames) {
        foreach ($declaredNames as $name) {
            $setter = 'set' . implode('', array_map('ucfirst', explode('_', $name)));
            if (!method_exists($object, $setter)) {
                throw new Exception("Object declared $name dependency, but setter method $setter was not found");
            }
            $object->$setter($this->getRecursive($name));
        }
        return $object;
    }

    /**
     * Return an initialized service.
     *
     *  @param $name string
     *  @return mixed
    **/
    private function getRecursive(string $name) {
        if ($name === self::SELF_NAME) {
            return $this;
        }
        if (!isset($this->services[$name])) {
            if (in_array($name, $this->usedNames)) {
                throw new Exception("Cyclic dependency found while resolving $name");
            }
            $this->usedNames[] = $name;
            if (!isset($this->initializers[$name])) {
                throw new Exception("No initializer for $name");
            }
            $this->services[$name] = $this->produceRecursive($this->initializers[$name]);
        }
        return $this->services[$name];
    }

    private function getParameterValues(\ReflectionFunctionAbstract $function) {
        return array_map(
            function ($parameter) {
                return $this->getRecursive($parameter->getName());
            },
            $function->getParameters()
        );
    }
}
