<?php

namespace LeoRalph\DependencyResolver;

class Container
{
    private array $classes;

    public function __construct(array $classes = [])
    {
        $this->classes = $classes;
    }

    public function addParamForClass(string $className, string $param, mixed $value)
    {
        if (!array_key_exists($className, $this->classes)) {
            $this->classes[$className] = [];
        }

        $this->classes[$className][$param] = $value;
    }

    public function addMultipleParamsForClass(string $className, array $params)
    {
        foreach ($params as $param => $value) {
            $this->addParamForClass($className, $param, $value);
        }
    }

    public function paramExistsForClass(string $className, string $param)
    {
        return array_key_exists($className, $this->classes)
            && array_key_exists($param, $this->classes[$className]);
    }

    public function getParam(string $className, string $param)
    {
        if (!$this->paramExistsForClass($className, $param)) {
            //
        }

        return $this->classes[$className][$param];
    }
}
