<?php

namespace Lemon;

class LemonMockParse
{
    protected $string;
    protected $match;
    public function __construct(String $string)
    {
        $this->string = $string;
        preg_match('/(.*)\(.*\)/', $this->string, $match);
        $this->match = $match;
    }
    public function toString()
    {
        return $this->isFunc() ? $this->funcName() : $this->string;
    }
    public function funcName()
    {
        return $this->match[1];
    }
    public function isFunc()
    {
        return $this->match[0] ?? false;
    }
}
