<?php
namespace Lemon;

class MockClassBody {
    public static function body() {
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
