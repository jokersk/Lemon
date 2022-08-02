<?php
namespace Lemon;
class MockLemon
{
    public $_attributes = [];
    public function addAttribute($key, $value) {
        $this->_attributes[$key] = $value;
        return $this;
    }

    public function __call($name, $arguments)
    {
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
}
