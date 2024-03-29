<?php

declare(strict_types=1);

namespace Idiosyncratic;

use DomainException;
use ErrorException;
use JsonSerializable;
use ReflectionClass;
use RuntimeException;
use const E_ERROR;
use function array_key_exists;
use function debug_backtrace;
use function in_array;
use function spl_object_hash;
use function sprintf;
use function strtoupper;

abstract class Enum implements JsonSerializable
{
    /** @var mixed */
    private $value;

    /** @var array<string, array<string, mixed>> */
    private static $valueCache = [];

    /** @var array<string, array<string, Enum>> */
    private static $instanceCache = [];

    /**
     * @param mixed $value
     */
    final public static function create($value) : self
    {
        if (in_array($value, self::values()) === false) {
            throw new DomainException(sprintf('%s is not a valid %s value', $value, static::class));
        }

        $class = static::class;

        return self::$instanceCache[$class][$value] ??
            self::$instanceCache[$class][$value] = new static($value);
    }

    /**
     * @return mixed
     */
    final public function value()
    {
        return $this->value;
    }

    final public function equals(Enum $otherEnum) : bool
    {
        return spl_object_hash($otherEnum) === spl_object_hash($this);
    }

    public function __toString() : string
    {
        return (string) $this->value();
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * @return array<string, mixed>
     */
    final public static function values() : array
    {
        $class = static::class;

        return self::$valueCache[$class] ??
            self::$valueCache[$class] = (new ReflectionClass($class))->getConstants();
    }

    /**
     * @param array<mixed> $arguments
     *
     * @return mixed
     */
    final public static function __callStatic(string $name, array $arguments)
    {
        $values = static::values();
        $key = strtoupper($name);
        if (array_key_exists($key, $values) ===  true) {
            return static::create($values[$key]);
        }

        throw new DomainException(sprintf('%s is not a valid %s value', $name, static::class));
    }

    /**
     * @param mixed $value
     */
    final private function __construct($value)
    {
        $this->value = $value;
    }
}
