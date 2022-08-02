<?php

namespace Lemon;

use Closure;
use ReflectionClass;

class Lemon
{
    protected static function createMockWithArray($paths)
    {
        $class = new MockLemon();
        foreach ($paths as $key => $value) {
            $class->_attributes += Lemon::createMock($key, $value)->_attributes;
        }
        return $class;
    }

    public static function createMock($paths, $result = null)
    {
        if (is_array($paths)) {
            return static::createMockWithArray($paths);
        }
        $class = new MockLemon();
        $paths = explode("->", $paths);
        $first = array_shift($paths);
        $first = new LemonMockParse($first);

        if (!count($paths)) {
            return $class->addAttribute($first->toString(), $result);
        }
        return $class->addAttribute($first->toString(), static::createMock(
            implode("->", $paths),
            $result
        ));
    }

    public static function invade($obj)
    {
        return Invade::execute($obj);
    }

    public static function setClassResolver(Closure $resolver) {
        LemonMockClass::setResolver($resolver);
    }

    public static function mockClass($className, $paths)
    {
        return (new LemonMockClass)->execute($className, $paths);
    }
}
