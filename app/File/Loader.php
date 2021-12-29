<?php

declare(strict_types=1);

namespace App\File;

class Loader
{
    private static $files = [];

    private static $folders = [
        ROUTE_PATH,
        CONFIG_PATH
    ];

    public static function loadFile(string $filePath)
    {
        if (!file_exists($filePath)) {
            return [];
        }

        if (!array_filter(static::$folders, function ($path) use ($filePath) {
            return strpos($filePath, $path) !== false;
        }, ARRAY_FILTER_USE_BOTH)) {
            return [];
        }

        if (empty(static::$files[$filePath])) {
            static::$files[$filePath] = require_once $filePath;
        }

        return static::$files[$filePath];
    }
}
