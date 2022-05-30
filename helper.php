<?php

declare(strict_types=1);

use App\{File\Cache, File\FileSystem, Application};
use Laminas\Diactoros\Response\HtmlResponse;

if (!function_exists('app')) {

    /**
     * @param string $name
     * @return mixed|Application
     */
    function app(string $name = 'app')
    {
        return Application::getInstance()->get($name);
    }
}

if (!function_exists('cache')) {
    /**
     * 文件缓存
     * @param string $prefix 缓存空间 默认 app
     * @return \App\File\Cache
     */
    function cache(string $prefix = 'BoQing'): Cache
    {
        return Cache::getInstance($prefix);
    }
}

if (!function_exists('fileSystem')) {
    /**
     * @param string $path  目录  默认 缓存目录
     * @return \App\File\FileSystem
     */
    function fileSystem(string $path = CACHE_PATH): FileSystem
    {
        return FileSystem::getInstance($path);
    }
}

if (!function_exists('view')) {
    /**
     * @param  string $name
     * @param  array  $data
     * @param  int  $status
     * @param  array  $headers
     *
     * @return \Laminas\Diactoros\Response\HtmlResponse
     */
    function view(string $name, array $data = [], int $status = 200, array $headers = []): HtmlResponse
    {
        return new HtmlResponse(app()->render($name, $data), $status, $headers);
    }
}
