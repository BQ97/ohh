<?php

declare(strict_types=1);

namespace App\File;

use App\Utils;
use Vtiful\Kernel\Excel;

class Xls
{
    /**
     * @param string filename   文件名
     * @param bool  uploadFile  是否为上传文件
     * @return array 第一个工作表的内容
     */
    public static function read(String $fileName, bool $isUploadFile = false, String $sheet = null)
    {
        if ($isUploadFile) {
            $config['path'] = '/';
        } else {
            $config['path'] = UPLOAD_PATH;
        }

        if (!file_exists($config['path'] . $fileName)) {
            return false;
        }

        $excel = new Excel($config);

        $sheetList = $excel->openFile($fileName)->sheetList();

        return $excel->openSheet($sheet ? $sheet : $sheetList[0])->setSkipRows(1)->getSheetData();
    }

    /**
     * @param array data 必须是干干净净的 二维数组
     * @return array  filepath（路径）filename（文件名）
     */
    public static function write(array $data, array $header = [], $fileName = null)
    {
        $config['path'] = UPLOAD_PATH;

        $excel = new Excel($config);

        if (!$fileName) {
            $fileName =  Utils::Uuid() . '.xlsx';
        }

        foreach ($data as $key => $value) {
            $data[$key] = array_values($value);
        }

        $filePath = $excel->fileName($fileName)->header($header)->data($data)->output();

        return [
            'filepath' => $filePath,
            'filename' => $fileName,
        ];
    }

    /**
     * 写入多个工作蒲
     * @param string $fileName
     * @param array $data
     */
    public static function writeMultipleSheetExcel(String $fileName, array $data)
    {
        $excel = new Excel([
            'path' => UPLOAD_PATH
        ]);

        $sheet1 = $data[0];

        unset($data[0]);

        if (empty($sheet1['sheet_name']) || !isset($sheet1['header']) || !isset($sheet1['data'])) {
            return false;
        }

        // 创建第一个工作表
        $fileObject = $excel->fileName($fileName, $sheet1['sheet_name']);

        $fileObject->header($sheet1['header'])->data($sheet1['data']);

        foreach ($data as $value) {
            if (empty($value['sheet_name']) || !isset($value['header']) || !isset($value['data'])) {
                return $fileObject->output();
            }

            // 追加工作表
            $fileObject->addSheet($value['sheet_name'])->header($value['header'])->data($value['data']);
        }

        return $fileObject->output();
    }

    /**
     *  php的日期值是    1970-01-01   开始计算       单位：秒
     *  EXCEL        是      1900-01-01   开始计算       单位：天
     *  25569是EXCEL的1970-01-01代表的数字
     *
     * @param int time excel的时间
     * @param string format  格式化，不填就转化成时间戳
     *
     * @return string 时间
     */
    public static function getExcelTime(int $time, string $format = '')
    {
        if ($time > 25569) {
            $time = ($time - 25569) * 24 * 60 * 60;
            $time = $format ? date($format, $time) : $time;
        } else {
            $time = $format ? '1970-01-01 08:00:00' : 0;
        }

        return $time;
    }
}
