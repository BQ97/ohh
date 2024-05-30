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

        $handle = fopen($fileName, 'r');

        $output  = [];
        $row = 0;
        while ($lineData = fgetcsv($handle)) {
            $num = count($lineData);
            for ($i = 0; $i < $num; $i++) {
                $from_encoding = mb_detect_encoding($lineData[$i], mb_detect_order(), true);
                if ($from_encoding !== 'UTF-8') {
                    $lineData[$i] = iconv('GBK', 'UTF-8', $lineData[$i]);
                }
                $output[$row][$i] = $lineData[$i];
            }
            $row++;
        }
        fclose($handle);
        return $output;
    }

    /**
     * @param  string	$fileName
     * @param  array	$data
     * @param  boolean	$download
     * 
     * @return string|DownloadResponse
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
