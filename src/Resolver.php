<?php

namespace LeoRalph\DependencyResolver;

use LeoRalph\DependencyResolver\Exception\ResolverException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class Resolver
{
    public function __construct(
        private Container $container
    ) {
    }

    /**
     * Resolves a class, then return the object.
     *
     * @param string $className
     * @return object
     */
    public function resolveClass(string $className): object
    {
        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();

        if (!$constructor) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $parameters = $constructor->getParameters();

        if (!$parameters) {
            return $reflectionClass->newInstance();
        }

        $arguments = $this->tryGetArguments($className, $parameters);

        return $reflectionClass->newInstanceArgs($arguments);
    }

    /**
     * Resolve a method from the given class, then returns it.
     *
     * @param string $className
     * @param string $method
     * @return mixed
     */
    public function resolveMethod(string $className, string $method)
    {
        $object = $this->resolveClass($className);
        $reflectionMethod = new ReflectionMethod($object, $method);

        $parameters = $reflectionMethod->getParameters();

        if (!$parameters) {
            return $reflectionMethod->invoke($object);
        }

        $arguments = $this->tryGetArguments($className, $parameters);

        return $reflectionMethod->invokeArgs($object, $arguments);
    }

    /**
     * Get arguments for given parameters.
     *
     * @param string $className
     * @param array $parameters
     * @return array
     */
    private function tryGetArguments(string $className, array $parameters): array
    {
        $arguments = [];

        foreach ($parameters as $parameter) {
            $arguments[] = $this->resolveParameter($className, $parameter);
        }

        return $arguments;
    }

    /**
     * Resolve a parameter for the given class, throws an Exception if the parameter is not found.
     *
     * @param string $className
     * @param ReflectionParameter $parameter
     * @throws \LeoRalph\DependencyResolver\Exceptions\ResolverException
     * @return mixed
     */
    private function resolveParameter(string $className, ReflectionParameter $parameter): mixed
    {
        $paramName = $parameter->getName();

        if ($this->container->paramExistsForClass($className, $paramName)) {
            return $this->container->getParam($className, $paramName);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if (!$parameter->hasType() && !$parameter->getType()->isBuiltin()) {
            throw new ResolverException("Cannot resolve param $paramName for class $className");
        }

        $type = $parameter->getType();

        return $this->resolveClass($type);
    }
}
