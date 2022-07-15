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
    public static function readLog(string $fileName)
    {
        $fileName = LOG_PATH . $fileName;

        if (!file_exists($fileName)) return false;

        $date = date('Y-m-d', strtotime(pathinfo($fileName, PATHINFO_FILENAME)));

        $content = file_get_contents($fileName);

        return array_map(fn ($item) => explode('|', $item), explode($date, $content));
    }

    public static function exportLog(string $fileName)
    {
        return \App\File\Excel::write([['title' => '日志', 'data' => static::readLog($fileName)]]);
    }

    public function __call($name, $arguments)
    {
        return static::__callStatic($name, $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        if (extension_loaded('seaslog')) {
            return call_user_func_array(['SeasLog', $name], $arguments);
        }

        $level = strtoupper($name);

        if (in_array($level, ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'ALERT', 'EMERGENCY'], true)) {

            $debug = debug_backtrace()[0];

            $message = [
                date('Y-m-d H:i:s'),
                $level,
                $debug['file'] . ' : ' . $debug['line'],
                $arguments[0] . PHP_EOL
            ];

            return error_log(implode(' | ', $message), 3, LOG_PATH . 'default/' . date('Ymd') . '.log');
        }
    }
}
