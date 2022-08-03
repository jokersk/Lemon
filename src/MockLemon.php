<?php
namespace Lemon;
class MockLemon
{
    public $_attributes = [];
    public $_methods = [];
    public function addAttribute(LemonMockParse $key, $value) {
        if ($key->isFunc()) {
            $this->_methods[$key->toString()] = $value;
        } else {
            $this->_attributes[$key->toString()] = $value;
        }
        return $this;
    }

    public function __call($name, $arguments)
    {
        if ( array_key_exists($name, $this->_methods) ) {
            return $this->_methods[$name];
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
