<?php

declare(strict_types=1);

namespace App\File;

class Loader
{
    /**
     * @var array 已经加载的文件
     */
    private static array $files = [];

    /**
     * @var array 允许加载的文件夹
     */
    private static array $folders = [
        ROUTE_PATH,
        CACHE_PATH,
        CONFIG_PATH
    ];

    public static function loadFile(string $filePath)
    {
        if (!file_exists($filePath)) {
            return [];
        }

        if (!array_filter(static::$folders, fn ($path) => strpos($filePath, $path) !== false, ARRAY_FILTER_USE_BOTH)) {
            return [];
        }

        if (empty(static::$files[$filePath])) {
            static::$files[$filePath] = require_once $filePath;
        }

        return static::$files[$filePath];
    }
}
