<?php

declare(strict_types=1);

namespace ProgPhil1337\DependencyInjection;

/**
 * ClassLookup
 *
 * @package ProgPhil1337\DependencyInjection
 * @author Philipp Lohmann <lohmann.philipp@gmx.net>
 */
final class ClassLookup
{

    /** @var array<string, object> */
    private array $lookup = [];

    /** @var array<string,bool> */
    private array $singletons = [];

    /** @var array<string,string> */
    private array $aliases = [];

    /**
     * @param object $o
     * @return $this
     */
    public function register(object $o): self
    {
        if (!$this->shouldDismiss($this->getResolvedClassName($o)) && !$this->isRegistered($o)) {

            $this->lookup[$this->getResolvedClassName($o)] = $o;
        }

        return $this;
    }

    /**
     * @param object|string $o
     * @return bool
     */
    public function shouldDismiss(object|string $o): bool
    {
        $className = $this->getClassName($o);

        if (
            array_key_exists($className, $this->singletons) ||
            array_key_exists($this->getResolvedClassName($className), $this->singletons)

        ) {
            return false;
        }

        foreach (array_keys($this->singletons) as $class) {
            $resolvedName = $this->getResolvedClassName($class);

            if (is_subclass_of($className, $resolvedName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param object|string $o
     * @return bool
     */
    public function isRegistered(object|string $o): bool
    {
        return array_key_exists($this->getResolvedClassName($o), $this->lookup);
    }

    /**
     * @param object|string $o
     * @return object|null
     */
    public function get(object|string $o): ?object
    {
        return $this->lookup[$this->getResolvedClassName($o)] ?? null;
    }

    /**
     * @param object|string $o
     * @return $this
     */
    public function singleton(object|string $o): self
    {
        $this->singletons[$this->getClassName($o)] = true;

        return $this;
    }

    /**
     * @param object|string $o
     * @return string
     */
    public function getResolvedClassName(object|string $o): string
    {
        $oName = $this->getClassName($o);

        if (array_key_exists($oName, $this->aliases)) {
            return $this->getResolvedClassName($this->aliases[$oName]);
        }

        return $oName;
    }

    /**
     * @param object|string $o
     * @return string
     */
    private function getClassName(object|string $o): string
    {
        return is_string($o) ? $o : get_class($o);
    }

    /**
     * @param string|object $class1
     * @param string|object $class2
     * @return $this
     */
    public function alias(string|object $class1, string|object $class2): self
    {
        $this->aliases[$this->getClassName($class1)] = $this->getClassName($class2);

        return $this;
    }

}