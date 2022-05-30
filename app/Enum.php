<?php

declare(strict_types=1);

namespace App;

use ReflectionClass;

class Enum
{
    final private function __construct()
    {
    }

    /**
     * @param string $constName
     * @param mixed
     */
    public static function from(string $constName)
    {
        return constant(get_called_class() . '::' . strtoupper($constName));
    }

    /**
     * 获取类中所有的常量
     * @return array
     */
    public static function values(): array
    {
        return (new ReflectionClass(get_called_class()))->getConstants();
    }

    /**
     * 获取类中所有常量的key
     * @return array
     */
    public static function keys(): array
    {
        return array_keys(static::values());
    }

    /**
     * 获取类中所有的常量的值
     * @return array
     */
    public static function toArray(): array
    {
        return array_values(static::values());
    }

    /**
     * 验证是否属于指定枚举类中的任意一个值
     * @param string $className 类名
     * @param mixed $value 待验证的值
     * @param bool $strict 严格模式 默认 false
     * @return bool
     */
    public static function check($value, bool $strict = false): bool
    {
        return in_array($value, static::toArray(), $strict);
    }
}
