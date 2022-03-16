<?php

declare(strict_types=1);

namespace App\File;

use App\Utils;
use Vtiful\Kernel\Excel;

/**
 * @deprecated 今后不在使用该类库，请使用 \App\File\Excel代替
 */
class Xls
{
    const TYPE_INT = Excel::TYPE_INT;

    const TYPE_DOUBLE = Excel::TYPE_DOUBLE;

    const TYPE_STRING = Excel::TYPE_STRING;

    const TYPE_TIMESTAMP = Excel::TYPE_TIMESTAMP;

    /**
     * @deprecated 今后不在使用该方法 请使用 \App\File\Excel::read()代替
     * @param string filename   文件名
     * @return array 第一个工作表的内容
     */
    public static function read(String $fileName, String $sheet = null, array $rowOption = [])
    {
        $config['path'] = DS;

        if (!file_exists($config['path'] . $fileName)) {
            return [];
        }

        $excel = new Excel($config);

        $sheetList = $excel->openFile($fileName)->sheetList();

        $sheet = $sheet ?: $sheetList[0];

        if (!$excel->existSheet($sheet)) {
            return [];
        }

        $excel = $excel->openSheet($sheet, Excel::SKIP_EMPTY_ROW);

        while (($row = $excel->nextRow($rowOption)) !== NULL) {
            yield $row;
        }

        return [];
    }

    /**
     * @deprecated 今后不在使用该方法 请使用 \App\File\Excel::write()代替
     * @param array data 必须是干干净净的 二维数组
     * @return array  filepath（路径）filename（文件名）
     */
    public static function write(array $data, array $header = [], $fileName = null)
    {
        $config['path'] = DS;

        $excel = new Excel($config);

        if (!$fileName) {
            $fileName =  strtoupper(Utils::Uuid() . '.xlsx');
        }

        foreach ($data as $key => $value) {
            $data[$key] = array_values($value);
        }

        $filePath = $excel->fileName($fileName)->header($header)->data($data)->protection('123456')->output();

        return [
            'filepath' => $filePath,
            'filename' => $fileName,
        ];
    }

    /**
     * 写入多个工作蒲
     * @deprecated 今后不在使用该类库
     * @param string $fileName
     * @param array $data
     */
    public static function writeMultipleSheetExcel(String $fileName, array $data)
    {
        $excel = new Excel([
            'path' => DS
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
}
