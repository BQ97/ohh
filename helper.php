<?php

declare(strict_types=1);

use App\{File\Cache, File\FileSystem, Application, Utils};
use Laminas\Diactoros\Response\HtmlResponse;
use Revolt\EventLoop;

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
        return app('view')->display($name, $data, $status, $headers);
    }
}

if (!function_exists('atom_next_id')) {
    /**
     * 全局唯一ID
     */
    function atom_next_id(): int
    {
        return (int)app('snow')->id();
    }
}

if (!function_exists('setInterval')) {
    /**
     * 类似 `javascript` 的 setInterval
     * @param callable $callback
     * @param float $time
     * @return string for use with clearInterval
     */
    function setInterval(callable $callback, float $time): string
    {
        $intervalId = EventLoop::repeat($time, \Closure::fromCallable($callback));

        EventLoop::getDriver()->isRunning() || EventLoop::run();

        return $intervalId;
    }
}

if (!function_exists('clearInterval')) {
    /**
     * 类似 `javascript` 的 clearInterval
     * @param string $interval
     * @return void
     */
    function clearInterval(string $interval): void
    {
        EventLoop::cancel($interval);
    }
}

if (!function_exists('setTimeout')) {
    /**
     * 类似 `javascript` 的 setTimeout
     * @param callable $callback(string) :void
     * @param float $time
     * @return string
     */
    function setTimeout(callable $callback, float $time): string
    {
        $timeoutId = EventLoop::delay($time, \Closure::fromCallable($callback));

        EventLoop::getDriver()->isRunning() || EventLoop::run();

        return $timeoutId;
    }
}

if (!function_exists('clearTimeout')) {
    /**
     * 类似 `javascript` 的 clearTimeout
     * @param string $timeout
     * @return void
     */
    function clearTimeout(string $timeout): void
    {
        EventLoop::cancel($timeout);
    }
}

if (!function_exists('send_mail')) {
    /**
     * @param array $param
     * @return bool
     */
    function send_mail(array|string $tos, string $subject, string $body, array|string $attachments = [], string $altBody = '')
    {
        /**
         * @var \PHPMailer\PHPMailer\PHPMailer $mail
         */
        $mail = app('mail');

        if (!$tos || !$subject || !$body) {
            return false;
        }

        $mail->isHTML($body !== strip_tags($body));
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;

        if (is_string($tos)) {
            $mail->addAddress($tos);
        } else {
            foreach ($tos as $to) {
                if (is_string($to)) {
                    $mail->addAddress($to);
                } else {
                    $mail->addAddress(...$to);
                }
            }
        }

        if (is_string($attachments)) {
            $mail->addAttachment($attachments);
        } else {
            foreach ($attachments as $value) {
                if (is_string($to)) {
                    $mail->addAttachment($value);
                } else {
                    $mail->addAttachment(...$value);
                }
            }
        }

        return $mail->send();
    }
}

if (!function_exists('console_log')) {
    /**
     * 类似 `javascript` 的 console.log
     */
    function console_log(mixed ...$vars): mixed
    {
        return dump(...$vars);
    }
}


if (!function_exists('jsonFormat')) {
    /**
     * @param string | array | object $json
     * @return string
     */
    function jsonFormat(string | array | object $json): string
    {
        return Utils::jsonFormat($json);
    }
}

if (!function_exists('pdf2Image')) {
    function pdf2Image(string $path, string $imageExt = 'png'): bool
    {
        return Utils::pdf2Image($path, $imageExt);
    }
}
