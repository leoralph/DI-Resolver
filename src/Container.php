<?php

namespace LeoRalph\DependencyResolver;

use LeoRalph\DependencyResolver\Exception\ContainerException;

class Container
{
    public function __construct(
        private array $classes = []
    ) {
    }

    /**
     * Adds a parameter in the container for the given class.
     *
     * @param string $className
     * @param string $param
     * @param mixed $value
     * @return mixed
     */
    public function addParamForClass(string $className, string $param, mixed $value)
    {
        if (!array_key_exists($className, $this->classes)) {
            $this->classes[$className] = [];
        }

        $this->classes[$className][$param] = $value;
    }

    /**
     * Adds multiple parameters in the container for the given class.
     *
     * @param string $className
     * @param array $params
     * @return void
     */
    public function addMultipleParamsForClass(string $className, array $params)
    {
        foreach ($params as $param => $value) {
            $this->addParamForClass($className, $param, $value);
        }
    }

    /**
     * Checks if the container has the parameter for the given class.
     *
     * @param string $className
     * @param string $param
     * @return bool
     */
    public function paramExistsForClass(string $className, string $param): bool
    {
        return array_key_exists($className, $this->classes)
            && array_key_exists($param, $this->classes[$className]);
    }

    /**
     * Get a parameter for the given class, throws an exception if the parameter does not exist.
     *
     * @param string $className
     * @param string $param
     * @return mixed
     * @throws \LeoRalph\DependencyResolver\Exceptions\ContainerException
     */
    public function getParam(string $className, string $param): mixed
    {
        if (!$this->paramExistsForClass($className, $param)) {
            throw new ContainerException("Param $param does not exist for class $className");
        }

        return $this->classes[$className][$param];
    }
}
