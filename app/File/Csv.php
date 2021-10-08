<?php
declare(strict_types=1);

namespace App\File;

class Csv
{
    /**
     * @param string $fileName 文件名
     * @return array|bool
     */
    public static function read(string $fileName)
    {
        //参数分析
        if (!$fileName) {
            return false;
        }

        setlocale(LC_ALL, 'en_US.UTF-8');

        //读取csv文件内容
        $handle = fopen($fileName, 'r');

        $outputArray  = [];
        $row = 0;
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $num = count($data);
            for ($i = 0; $i < $num; $i++) {
                $outputArray[$row][$i] = iconv('GB18030', 'UTF-8', trim($data[$i]));
            }
            $row++;
        }
        fclose($handle);
        return $outputArray;
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

        $content = '';
        foreach ($data as $lines) {
            if ($lines && is_array($lines)) {
                foreach ($lines as $key => $value) {
                    if (is_string($value)) {
                        $lines[$key] = '"' . iconv('UTF-8', 'GB18030', $value) . '"';
                    }
                }
                $content .= implode(',', $lines) . "\n";
            }
        }

        if ($download) {
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Expires:0");
            header("Pragma:public");
            header("Cache-Control: public");
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=" . $fileName);

            echo $content;
        } else {

            $fileName = date('Ymd') . DS . $fileName;

            fileSystem(UPLOAD_PATH)->writeFile($fileName, $content);

            return UPLOAD_PATH . $fileName;
        }
    }
}
