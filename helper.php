<?php

use App\Container;
use App\File\Cache;
use Laminas\Diactoros\Response\HtmlResponse;

if (!function_exists('app')) {
    /**
     * 快速获取容器中的实例 支持依赖注入
     * @param string    $name 类名或标识 默认获取当前应用实例
     * @param array     $args 参数
     * @param bool      $newInstance    是否每次创建新的实例
     * @return mixed|\App\Application
     */
    function app($name = 'app', $args = [], $newInstance = false)
    {
        return Container::getInstance()->make($name, $args, $newInstance);
    }
}

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
     * @return \App\File\Flysystem
     */
    function fileSystem(string $path = CACHE_PATH): \App\File\FileSystem
    {
        return app()->fileSystem($path);
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
    function view(string $name, array $data = [], int $status = 200, array $headers = []): \Laminas\Diactoros\Response\HtmlResponse
    {
        return new HtmlResponse(app()->render($name, $data, true), $status, $headers);
    }
}

if (!function_exists('router')) {
    /**
     * @return \App\Router\Router
     */
    function router(): \App\Router\Router
    {
        return app('\\App\\Router\\Router');
    }
}
