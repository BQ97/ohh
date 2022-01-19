<?php

namespace App;

/**
 * @see https://github.com/SeasX/SeasLog/blob/master/README_zh.md
 * @link https://www.php.net/manual/zh/class.seaslog.php
 * 
 * @method static bool alert(string $message, array $content = ?, string $logger = ?)
 * @method static mixed analyzerCount(string $level, string $log_path = ?, string $key_word = ?)
 * @method static mixed analyzerDetail( string $level, string $log_path = ?, string $key_word = ?, int $start = ?, int $limit = ?, int $order = ?)
 * @method static bool closeLoggerStream(int $model, string $logger)
 * @method static bool critical(string $message, array $content = ?, string $logger = ?)
 * @method static bool debug(string $message, array $content = ?, string $logger = ?)
 * @method static bool emergency(string $message, array $content = ?, string $logger = ?)
 * @method static bool error(string $message, array $content = ?, string $logger = ?)
 * @method static bool flushBuffer()
 * @method static string Seaslog::getBasePath()
 * @method static array getBuffer()
 * @method static bool getBufferEnabled()
 * @method static string getDatetimeFormat()
 * @method static string getLastLogger()
 * @method static string getRequestID()
 * @method static bool getRequestVariable(int $key)
 * @method static bool info(string $message, array $content = ?, string $logger = ?)
 * @method static bool log( string $level, string $message = ?, array $content = ?, string $logger = ?)
 * @method static bool notice(string $message, array $content = ?, string $logger = ?)
 * @method static bool setBasePath(string $base_path)
 * @method static bool setDatetimeFormat(string $format)
 * @method static bool setLogger(string $logger)
 * @method static bool setRequestID(string $request_id)
 * @method static bool setRequestVariable(int $key, string $value)
 * @method static bool warning(string $message, array $content = ?, string $logger = ?)
 */
class Logger
{
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array("SeasLog::{$name}", $arguments);
    }
}
