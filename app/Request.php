<?php

declare(strict_types=1);

namespace App;

use App\Utils;

class Request
{
    /**
     * 获取input数据
     *
     * @return array
     */
    private function input()
    {
        $content = file_get_contents('php://input');

        if (false !== strpos($this->server('CONTENT_TYPE', ''), 'application/json') || 0 === strpos($content, '{"')) {
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
    public function server(String $key = '', $default = null)
    {
        return Utils::getData($_SERVER, $key, $default);
    }

    /**
     * @param string
     * @return string|array
     */
    public function getRequest(String $key = '', $default = null)
    {
        return Utils::getData($_REQUEST, $key, $default);
    }

    /**
     * @param string
     * @return string|array
     */
    public function post(String $key = '', $default = null)
    {
        return Utils::getData(empty($_POST) ? $this->input() : $_POST, $key, $default);
    }

    /**
     * @param string
     * @return string|array
     */
    public function get(String $key = '', $default = null)
    {
        return Utils::getData($_GET, $key, $default);
    }

    /**
     * @param string
     * @return string|array
     */
    public function files(String $key = '', $default = null)
    {
        return Utils::getData($_FILES, $key, $default);
    }
}
