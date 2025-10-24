<?php

namespace App;

use Monolog\Level;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    /**
     * @var array<string,Monolog>
     */
    private static array $loggers = [];

    private static string $base_path = __DIR__ . DS . 'log' . DS;

    private static string $logger = 'default';

    private static function handler()
    {
        // 使用 logger 名称 + 日期 作为缓存 key，确保每个 logger 每天生成一个新的日志文件
        $currentDate = date('Ymd');
        $loggerKey = static::$logger . '_' . $currentDate;

        // 如果缓存中不存在该 logger 实例，创建新的
        if (!isset(static::$loggers[$loggerKey])) {

            $dateFormat = "Y-m-d H:i:s";
            $output = "%datetime% | %level_name% | %message% | %context% | %extra%\n";
            $formatter = new LineFormatter($output, $dateFormat);

            // 日志文件路径
            $logFilePath = static::$base_path . DS . static::$logger . DS . $currentDate . '.log';

            // 检查并创建日志目录（如果不存在）
            $logDir = dirname($logFilePath);
            is_dir($logDir) || mkdir($logDir, 0755, true);

            // 创建 StreamHandler，并设置 formatter
            $stream = new StreamHandler($logFilePath, Level::Debug);
            $stream->setFormatter($formatter);

            // 创建 Monolog 实例并添加 handler
            $logger = new Monolog(static::$logger);
            $logger->pushHandler($stream);

            // 缓存该实例，避免同一天重复创建
            static::$loggers[$loggerKey] = $logger;
        }

        // 返回缓存的 logger 实例
        return static::$loggers[$loggerKey];
    }

    public static function readLog(string $fileName)
    {
        $fileName = realpath(LOG_PATH . $fileName);

        if (!$fileName || !file_exists($fileName)) return false;

        $datePattern = '/\d{4}-\d{2}-\d{2}/';
        $content = file_get_contents($fileName);

        // 匹配日志中的日期，确保日志格式一致
        preg_match_all($datePattern, $content, $dates);

        return array_map(function ($item) {
            // 将每一行按照日志格式进行分割
            $data = explode('|', $item);
            return [
                'datetime' => $data[0] ?? '',
                'level' => $data[1] ?? '',
                'message' => $data[2] ?? '',
                'context' => $data[3] ?? '',
                'extra' => $data[4] ?? ''
            ];
        }, explode($dates[0][0], $content));
    }

    public static function getBasePath()
    {
        return static::$base_path;
    }

    public static function setBasePath(string $base_path)
    {
        static::$base_path = $base_path;

        return true;
    }

    public static function setLogger(?string $logger = null)
    {
        if ($logger === static::$logger) {
            return true;
        }

        // 需要去除空格，特殊字符，并限制长度（如50个字符以内）
        $logger = preg_replace('/\s+|[\/\\?%*:|"<>]/u', '', $logger ?? static::$logger);

        if (!$logger) {
            throw new \InvalidArgumentException('Logger name is required.');
        }

        if (strlen($logger) > 50) {
            throw new \InvalidArgumentException('Logger name is too long (max 50 characters).');
        }

        static::$logger = $logger;

        return true;
    }

    public static function alert(string $message, array $content = [], ?string $logger = null)
    {
        static::setLogger($logger);

        static::handler()->alert($message, $content);
    }

    public static function critical(string $message, array $content = [], ?string $logger = null)
    {
        static::setLogger($logger);

        static::handler()->critical($message, $content);
    }

    public static function debug(string $message, array $content = [], ?string $logger = null)
    {
        static::setLogger($logger);

        static::handler()->debug($message, $content);
    }

    public static function emergency(string $message, array $content = [], ?string $logger = null)
    {
        static::setLogger($logger);

        static::handler()->emergency($message, $content);
    }

    public static function error(string $message, array $content = [], ?string $logger = null)
    {
        static::setLogger($logger);

        static::handler()->error($message, $content);
    }

    public static function info(string $message, array $content = [], ?string $logger = null)
    {
        static::setLogger($logger);

        static::handler()->info($message, $content);
    }

    public static function notice(string $message, array $content = [], ?string $logger = null)
    {
        static::setLogger($logger);

        static::handler()->notice($message, $content);
    }

    public static function warning(string $message, array $content = [], ?string $logger = null)
    {
        static::setLogger($logger);

        static::handler()->warning($message, $content);
    }

    public static function log(string $level, string $message, array $content = [], ?string $logger = null)
    {
        static::setLogger($logger);

        static::handler()->log($level, $message, $content);
    }

    public function __debugInfo()
    {
        return [
            'base_path' => static::$base_path,
            'logger' => static::$logger,
        ];
    }
}
