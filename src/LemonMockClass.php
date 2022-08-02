<?php

namespace Lemon;

use Closure;
use ReflectionClass;
use ReflectionParameter;

class LemonMockClass
{
    protected $attributeToMock = [];
    protected $properties = '';
    protected $methods = '';
    public static $count = 0;
    public static $resolver = null;

    /**
     * @var ReflectionClass $reflectClass
     */
    protected $reflectClass;

    public function execute($className, $paths)
    {
        $this->reflectClass = new ReflectionClass($className);

        foreach ($paths as $key => $value) {
            if ($this->isMethod($key)) {
                $this->createMethods($key, $value);
                continue;
            }
            $this->createProperties($key, $value);
        }

        $magics = $this->magics();

        static::$count += 1;

        $count = static::$count;

        $class = $className . $count;
        eval(<<<M
            class $class extends $className { 
                $this->properties
                $magics
                $this->methods
            };
        M);

        $instance = $this->resolverInstance($class);

        $instance->_set_attributes(Lemon::createMock($this->attributeToMock)->_attributes);

        return $instance;
    }

    public static function setResolver(Closure $resolver)
    {
        static::$resolver = $resolver;
    }

    protected function resolverInstance(string $className)
    {
        if (static::$resolver && is_callable(static::$resolver)) {
            return call_user_func(static::$resolver, $className);
        }
        return new $className;
    }

    protected function createProperties($key, $value)
    {
        try {
            if ($this->reflectClass->getProperty($key)) {
                $this->properties .= 'public $' . $key . ' = ' . $value . ';';
            }
        } catch (\Exception $e) {
            $this->attributeToMock[$key] = $value;
        }
    }

    protected function createMethods($key, $value)
    {
        try {
            $methodName = preg_replace('/(.*)\(.*\)/', '$1', $key);
            if ($currentMethod = $this->reflectClass->getMethod($methodName)) {
                $params = $currentMethod->getParameters();
                $methodParams = [];
                /** @var ReflectionParameter $param */
                foreach ($params as $param) {
                    $methodParams[] = (new ParamHandler($param))->handle();
                }
                $methodParams = implode(',', $methodParams);
                $returnType = $currentMethod->getReturnType() ? ':' . $currentMethod->getReturnType()->getName() : '';
                $this->methods .= <<<METHOD
                public function $methodName($methodParams) $returnType {
                    return \$this->__call('$methodName', func_get_args());
                }
                METHOD;
            }
        } catch (\Exception $e) {
        }
        $this->attributeToMock[$key] = $value;
    }

    protected function isMethod(string $key)
    {
        preg_match('/.*\(.*\)/', $key, $match);
        return (bool) count($match);
    }

    protected function magics()
    {
        return <<<'MAGIC'
                public $_attributes = [];
                protected $_methods = [];

                public function _set_attributes($value) {
                    $this->_attributes = $value;
                }

                public function setMethod($key, $value) {
                    $this->_methods[$key] = \Closure::bind($value, $this);
                }

                public function __call($name, $arguments)
                {
                    if ( array_key_exists($name, $this->_methods) ) {
                        return $this->_methods[$name](...$arguments);
                    }
                    if ( array_key_exists($name, $this->_attributes) ) {
                        return $this->_attributes[$name];
                    }
                    return function() {};
                }

                public function __get($name)
                {
                    if ( array_key_exists($name, $this->_attributes) ) {
                        return $this->_attributes[$name];
                    }
                    return '';
                }
            MAGIC;
    }
}
