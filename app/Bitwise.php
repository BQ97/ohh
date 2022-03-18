<?php

declare(strict_types=1);

namespace App;

class Bitwise
{
    /**
     * @param int $rowInt
     * @param int[] $items
     *
     * @return int
     */
    public static function insert(int $rowInt, int ...$items): int
    {
        return array_reduce($items, fn (int $carry, int $item) => $carry | $item, $rowInt);
    }

    /**
     * @param int $rowInt
     * @param int[] $items
     *
     * @return int
     */
    public static function delete(int $rowInt, int ...$items): int
    {
        return array_reduce($items, fn (int $carry, int $item) => $carry & (~$item), $rowInt);
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

    /**
     * @param int $rowInt
     * @param int[] $items
     *
     * @return array
     */
    public static function checkMultiple(int $rowInt, array $items)
    {
        return array_reduce($items, function (array $carry, int $item) use ($rowInt) {
            $carry[$item] = static::check($rowInt, $item);
            return $carry;
        }, []);
    }

    /**
     * @param int $rowInt
     * @return array
     */
    public static function decbin(int $rowInt): array
    {
        $row = decbin($rowInt);
        return ['row' => $row, 'arr' => str_split($row, 1)];
    }
}
