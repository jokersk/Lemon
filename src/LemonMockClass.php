<?php

namespace Lemon;

use ReflectionClass;
use ReflectionParameter;

class LemonMockClass
{
    protected $attributeToMock = [];
    protected $properties = '';
    protected $methods = '';

    /**
     * @var ReflectionClass $reflectClass
     */
    protected $reflectClass;

    public function execute($className, $paths)
    {
        $this->reflectClass = new ReflectionClass($className);
        $properties = '';

        foreach ($paths as $key => $value) {
            preg_match('/.*\(.*\)/', $key, $match);
            if (!count($match)) {
                $this->createProperties($key, $value);
                continue;
            }

            $this->createMethods($key, $value);
        }

        $magics = $this->magics();

        $class = eval(<<<M
            return new class extends $className { 
                $this->properties
                $magics
                $this->methods
            };
        M);

        $class->_set_attributes(Lemon::createMock($this->attributeToMock)->_attributes);

        return $class;
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
                    $methodParams[] = $param->getType().' $'. $param->getName();
                }
                $methodParams = implode(',', $methodParams);
                $returnType = $currentMethod->getReturnType() ? ':'. $currentMethod->getReturnType()->getName() : '';
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
