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

    final public function __toString() : string
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
     * @param mixed $value
     */
    final private function __construct($value)
    {
        $this->value = $value;
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

        $trace = debug_backtrace();

        throw new ErrorException(
            sprintf(
                'Call to undefined method %s%s%s()',
                $trace[0]['class'],
                $trace[0]['type'],
                $name
            ),
            0,
            E_ERROR,
            $trace[0]['file'],
            $trace[0]['line']
        );
    }
}
