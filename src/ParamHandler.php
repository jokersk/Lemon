<?php

namespace Lemon;

use ReflectionParameter;

class ParamHandler
{
    protected $param;
    protected $paramStr;

    public function __construct(ReflectionParameter $param)
    {
        $this->param = $param;
    }

    public function handle(): string
    {
        $this->paramStr = $this->param->getType() . ' $' . $this->param->getName();

        if ($this->param->isDefaultValueAvailable()) {
            $default = $this->param->getDefaultValue();
            $this->handleString($default);
            $this->handleNumber($default);
            $this->handleNUll($default);
            $this->handleBoolean($default);
        }
        return $this->paramStr;
    }

    protected function handleString($default)
    {
        if (gettype($default) !== 'string') return;
        if (!$default) {
            $this->paramStr .= ' = ""';
            return;
        }
        $this->paramStr .= <<<S
        = '$default'
        S;
    }

    protected function handleNumber($default)
    {
        if (!in_array(gettype($default), [ 'integer', 'double' ])) return;
        $this->paramStr .= ' = '. $default;
        return;
    }

    protected function handleNUll($default)
    {
        if (!in_array(gettype($default), [ 'NULL' ])) return;
        $this->paramStr .= ' = null';
        return;
    }

    protected function handleBoolean($default)
    {
        if (!in_array(gettype($default), [ 'boolean' ])) return;
        $value = $default ? 'true': 'false';
        $this->paramStr .= <<<b
        = $value
        b;
        return;
    }
}
