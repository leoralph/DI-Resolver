<?php

namespace LeoRalph\DependencyResolver;

use LeoRalph\DependencyResolver\Exception\ResolverException;
use ReflectionClass;
use ReflectionParameter;

class Resolver
{
    public function __construct(private Container $container)
    {
    }

    public function resolve(string $className)
    {
        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return $reflection->newInstanceWithoutConstructor();
        }

        $parameters = $constructor->getParameters();

        if (!$parameters) {
            return $reflection->newInstance();
        }

        $arguments = $this->tryGetArguments($className, $parameters);

        return $reflection->newInstanceArgs($arguments);
    }

    private function tryGetArguments(string $className, array $parameters)
    {
        $arguments = [];

        foreach ($parameters as $param) {
            $this->resolveParam($className, $arguments, $param);
        }

        return $arguments;
    }

    private function resolveParam(string $className, array &$arguments, ReflectionParameter $param)
    {
        $paramName = $param->getName();

        if ($this->container->paramExistsForClass($className, $paramName)) {
            $arguments[] = $this->container->getParam($className, $paramName);
            return;
        }

        if ($param->isDefaultValueAvailable()) {
            $arguments[] = $param->getDefaultValue();
            return;
        }

        if (!$param->hasType() && !$param->getType()->isBuiltin()) {
            throw new ResolverException("Cannot resolve param $param for class $className");
        }

        $type = $param->getType();

        $arguments[] = $this->resolve($type);
    }
}
