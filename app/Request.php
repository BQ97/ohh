<?php

declare(strict_types=1);

namespace App;

use App\Utils;
use Laminas\Diactoros\ServerRequestFactory;

class Request
{
    /**
     * 获取input数据
     *
     * @return array
     */
    private static function input()
    {
        $content = file_get_contents('php://input');

        if (false !== strpos(static::server('CONTENT_TYPE', ''), 'application/json') || 0 === strpos($content, '{"')) {
            return json_decode($content, true);
        } elseif (strpos($content, '=')) {
            parse_str($content, $data);
            return $data;
        }

        return $content ? $content : [];
    }

    /**
     * @param string
     * @return string|array
     */
    public static function server(String $key = '', $default = null)
    {
        return Utils::getData($_SERVER, $key, $default);
    }

    /**
     * @param string
     * @return string|array
     */
    public static function cookie(String $key = '', $default = null)
    {
        return Utils::getData($_COOKIE, $key, $default);
    }

    /**
     * @param string
     * @return string|array
     */
    public static function getRequest(String $key = '', $default = null)
    {
        return Utils::getData($_REQUEST, $key, $default);
    }

    /**
     * @param string
     * @return string|array
     */
    public static function post(String $key = '', $default = null)
    {
        return Utils::getData(empty($_POST) ? static::input() : $_POST, $key, $default);
    }

    /**
     * @param string
     * @return string|array
     */
    public static function get(String $key = '', $default = null)
    {
        return Utils::getData($_GET, $key, $default);
    }

    /**
     * @param string
     * @return string|array
     */
    public static function files(String $key = '', $default = null)
    {
        return Utils::getData($_FILES, $key, $default);
    }

    /**
     * @return \Laminas\Diactoros\ServerRequest
     */
    public static function createServerRequest()
    {
        return ServerRequestFactory::fromGlobals(
            static::server(),
            static::get(),
            static::post(),
            static::cookie(),
            static::files()
        );
    }
}
