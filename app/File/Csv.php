<?php

declare(strict_types=1);

namespace App\File;

use App\Response\DownloadResponse;
use ZipArchive;

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

    public static function arr2csv(array $data, string $to_encoding = 'UTF-8')
    {
        return array_reduce($data, function ($current, $items) use ($to_encoding) {

            $lines = array_map(function ($string) use ($to_encoding) {
                if (is_string($string)) {
                    return '"' . mb_convert_encoding($string, $to_encoding) . '"';
                }
                return $string;
            }, $items);

            return $current . implode(',', $lines) . "\r\n";
        }, '');
    }

    /**
     * @param  string	$fileName
     * @param  array	$data
     * @param  boolean	$download
     * 
     * @return string|DownloadResponse
     */
    public static function write(string $fileName, array $data, string $to_encoding = 'UTF-8')
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.csv';

        $fileName = date('Ymd') . DS . $fileName;

        FileSystem::getInstance(UPLOAD_PATH)->write($fileName, static::arr2csv($data, $to_encoding));

        return UPLOAD_PATH . $fileName;
    }

    public static function multiWrite(array $options, ?string $fileName = null)
    {
        if ($fileName) {
            $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.zip';
        } else {
            $fileName = atom_next_id() . '.zip';
        }

        $Ymd = date('Ymd');

        FileSystem::getInstance(UPLOAD_PATH)->mkDir($Ymd);

        $dir = UPLOAD_PATH . $Ymd . DS;

        $filePath = $dir . $fileName;

        $zip = new ZipArchive;

        if ($zip->open($filePath, ZipArchive::CREATE) !== true) {
            return false;
        }

        foreach ($options as $value) {
            if (empty($value['data'])) {
                continue;
            }
            if (empty($value['name'])) {
                $value['name'] = atom_next_id();
            }

            $zip->addFromString(pathinfo($value['name'], PATHINFO_FILENAME) . '.csv', static::arr2csv($value['data'], $value['to_encoding'] ?? 'UTF-8'));
        }

        $zip->close();

        return $filePath;
    }
}
