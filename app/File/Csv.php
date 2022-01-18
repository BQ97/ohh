<?php

declare(strict_types=1);

namespace App\File;

use App\Response\DownloadResponse;

class Csv
{
    /**
     * @param string $fileName 文件名
     * @return array|bool
     */
    public static function read(string $fileName)
    {
        if (!file_exists($fileName)) {
            return false;
        }

        return array_map(function ($string) {

            $from_encoding = mb_detect_encoding($string, mb_detect_order(), true);

            if ($from_encoding === false) {
                $string = iconv('GB18030', 'UTF-8', $string);
            } else {
                $string = mb_convert_encoding($string, 'UTF-8', $from_encoding);
            }

            return str_getcsv($string);
        }, file($fileName, FILE_USE_INCLUDE_PATH | FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }

    /**
     * @param  string	$fileName
     * @param  array	$data
     * @param  boolean	$download
     * @return string
     */
    public static function write(string $fileName, array $data, bool $download = true)
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.csv';

        $content = array_reduce($data, function ($current, $items) {

            $lines = array_map(fn ($string) => '"' . $string . '"', $items);

            return $current . implode(',', $lines) . PHP_EOL;
        }, '');

        if ($download) {

            return new DownloadResponse($content, $fileName);
        } else {

            $fileName = date('Ymd') . DS . $fileName;

            FileSystem::getInstance(UPLOAD_PATH)->write($fileName, $content);

            return UPLOAD_PATH . $fileName;
        }
    }
}
