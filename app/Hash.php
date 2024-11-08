<?php

declare(strict_types=1);

namespace App;

class Hash
{
    public static function make(string $value)
    {
        return password_hash($value, PASSWORD_BCRYPT, [
            'cost' => 10
        ]);
    }

    public static function check(string $value, string $hashedValue)
    {
        return password_verify($value, $hashedValue);
    }

    public static function info(string $hashedValue)
    {
        return password_get_info($hashedValue);
    }

    public static function needsRehash(string $hashedValue)
    {
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
            'cost' => 10,
        ]);
    }
}
