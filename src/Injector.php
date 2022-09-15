<?php

declare(strict_types=1);

namespace ProgPhil1337\DependencyInjection;

/**
 * Injector
 *
 * @package ProgPhil1337\DependencyInjection
 * @author Philipp Lohmann <lohmann.philipp@gmx.net>
 */
final class Injector
{
    public function __construct(private readonly ClassLookup $lookup)
    {
    }

    /**
     * @param string $className
     * @param array $methods
     * @return ?object
     */
    public function create(string $className, array $methods = []): ?object
    {
        if (!$this->lookup->isRegistered($className)) {
            try {
                $reflectionClass = new \ReflectionClass($className);
            } catch (\ReflectionException $e) {
                throw new \RuntimeException(sprintf(
                    'Unable to create class %s:%s%s',
                    $className,
                    PHP_EOL,
                    $e->getTraceAsString()
                ));
            }
            $constructor = $reflectionClass->getConstructor();

            if ($constructor === null) {
                try {
                    $instance = $reflectionClass->newInstance();
                } catch (\ReflectionException $e) {
                    throw new \RuntimeException(sprintf(
                        'Error creating reflection without args: %s',
                        $e->getMessage()
                    ));
                }

                $this->lookup->register($instance);
            } else {
                if ($constructor->isPrivate()) {
                    throw new \RuntimeException('Cannot create private constructors');
                }

                $dependencies = [];

                if (!array_key_exists('__construct', $methods)) {
                    foreach ($constructor->getParameters() as $parameter) {
                        $name = $this->lookup->getResolvedClassName($parameter->getType()->getName());

                        $dependency = $this->create($name);

                        $this->lookup->register($dependency);

                        $dependencies[] = $dependency;
                    }
                } else {
                    $dependencies = $methods['__construct'];
                    unset($methods['__construct']);
                }

                try {
                    $instance = $reflectionClass->newInstanceArgs($dependencies);

                    foreach ($methods as $method => $args) {
                        call_user_func_array([$instance, $method], $args);
                    }

                    $this->lookup->register($instance);
                } catch (\ReflectionException $e) {
                    throw new \RuntimeException(sprintf(
                        'Error creating reflection with args: %s',
                        $e->getMessage()
                    ));
                }
            }

        } else {
            $instance = $this->lookup->get($className);
        }

        return $instance;
    }

    /**
     * @return \ProgPhil1337\DependencyInjection\ClassLookup
     */
    public function getLookup(): ClassLookup
    {
        return $this->lookup;
    }
}