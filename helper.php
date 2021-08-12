<?php

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Filesystem;
use App\Cache;

if (!function_exists('cache')) {
    /**
     * 文件缓存
     * @param string $prefix 缓存空间 默认 app
     * @return Cache
     */
    function cache(string $prefix = 'BoQing'): Cache
    {
        return Cache::getInstance($prefix);
    }
}

if (!function_exists('fileSystem')) {
    /**
     * @param string $path  目录  默认 缓存目录
     * @return \League\Flysystem\Filesystem
     */
    function fileSystem($path = CACHE_PATH)
    {
        // The internal adapter
        $adapter = new LocalFilesystemAdapter(
            // Determine the root directory
            $path,
            // Customize how visibility is converted to unix permissions
            PortableVisibilityConverter::fromArray([
                'file' => [
                    'public' => 0640,
                    'private' => 0604,
                ],
                'dir' => [
                    'public' => 0740,
                    'private' => 7604,
                ],
            ]),

            // Write flags
            LOCK_EX,
            // How to deal with links, either DISALLOW_LINKS or SKIP_LINKS
            // Disallowing them causes exceptions when encountered
            LocalFilesystemAdapter::DISALLOW_LINKS
        );

        // The FilesystemOperator
        return new Filesystem($adapter);
    }
}
