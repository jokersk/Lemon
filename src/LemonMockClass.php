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
    protected $initProperties = [];

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

        $class = $this->getClassName($className) . $count;

        eval(<<<M
            class $class extends $className { 
                $this->properties
                $magics
                $this->methods
            };
        M);

        $instance = $this->resolverInstance($class);

        $instance->_set_attributes(Lemon::createMock($this->attributeToMock)->_attributes);

        $this->initialProperties($instance);

        return $instance;
    }

    protected function getClassName(string $className) {
        $parts = explode('\\', $className);
        return array_pop($parts);
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

    protected function initialProperties($instance) {
        foreach($this->initProperties as $key => $value) {
            $instance->$key = $value;
        }
    }

    protected function createProperties($key, $value)
    {
        try {
            if ($this->reflectClass->getProperty($key)) {
                $this->initProperties[$key] = $value;
                $this->properties .= 'public $' . $key . ' = null;';
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
        } catch (\Exception $e) { }
        $this->attributeToMock[$key] = $value;
    }

    protected function isMethod(string $key)
    {
        preg_match('/.*\(.*\)/', $key, $match);
        return (bool) count($match);
    }

    protected function magics()
    {
        return MockClassBody::body();
    }
}
