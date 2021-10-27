<?php

declare(strict_types=1);

namespace App;

class Bitwise
{
    /**
     * @param int $rowInt
     * @param int $items
     * 
     * @return int
     */
    public static function insert(int $rowInt, int ...$items): int
    {
        return array_reduce($items, function ($carry, $item) {
            return $carry | $item;
        }, $rowInt);
    }

    /**
     * @param int $rowInt
     * @param int $items
     * 
     * @return int
     */
    public static function delete(int $rowInt, int ...$items): int
    {
        return array_reduce($items, function ($carry, $item) {
            return $carry & (~$item);
        }, $rowInt);
    }

    /**
     * @param int $rowInt
     * @param int $item
     * 
     * @return bool
     */
    public static function check(int $rowInt, int $item): bool
    {
        return ($rowInt & $item) === $item;
    }

    public static function decbin(int $rowInt): array
    {
        $row = decbin($rowInt);
        return ['row' => $row, 'arr' => str_split($row, 1)];
    }
}
