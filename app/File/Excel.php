<?php

declare(strict_types=1);

namespace App\File;

use PhpOffice\PhpSpreadsheet\{IOFactory, Spreadsheet};

/**
 * PHPExcel
 * 官网Api文档 https://phpoffice.github.io/PhpSpreadsheet/master/
 * githhub地址  https://github.com/PHPOffice/PhpSpreadsheet
 */
class Excel
{
    /**
     *  php的日期值是    1970-01-01   开始计算       单位：秒
     *  EXCEL        是      1900-01-01   开始计算       单位：天
     *  25569是EXCEL的1970-01-01代表的数字
     *
     * @param int $time excel的时间
     * @param string $format  格式化，不填就转化成时间戳
     *
     * @return string 时间
     */
    public static function getExcelTime(int $time, string $format = '')
    {
        if ($time > 25569) {
            $time = ($time - 25569) * 24 * 60 * 60;
            $time = $format ? date($format, $time) : $time;
        } else {
            $time = '';
        }

        return $time;
    }

    /**
     * 读取 Excel 文件
     * @param string   $fileName 	文件名
     * @param string   $sheet 	    工作铺
     *
     * @return array
     */
    public static function read(string $fileName, string $sheet = null, $nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false)
    {
        $spreadsheet = IOFactory::load($fileName);

        $worksheet = $spreadsheet->getActiveSheet();

        if ($sheet) {

            if (!$spreadsheet->sheetNameExists($sheet)) {
                return [];
            }

            $worksheet = $spreadsheet->getSheetByName($sheet);
        }

        return $worksheet->toArray($nullValue, $calculateFormulas, $formatData, $returnCellRef);
    }

    /**
     * 写入 Excel 文件
     * @param array[] 	$options  配置
     * @param string 	$fileName  文件名字
     *
     * @return string
     */
    public static function write(array $options, string $fileName = null)
    {
        $spreadsheet = new Spreadsheet();

        foreach ($options as $key => $item) {

            $sheet = $key ? $spreadsheet->createSheet() : $spreadsheet->getActiveSheet();

            if (is_callable($item)) {
                call_user_func($item, $sheet);
            } else {
                if (!empty($item['title'])) {
                    $sheet->setTitle($item['title']);
                }

                if (!empty($item['data'])) {
                    $sheet->fromArray($item['data']);
                }
            }
        }

        $fileName = $fileName ?: atom_next_id();

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        FileSystem::getInstance(UPLOAD_PATH)->mkDir(date('Ymd'));

        $writer->save($path = UPLOAD_PATH . date('Ymd') . DS . pathinfo($fileName, PATHINFO_FILENAME) . '.xlsx');

        return $path;
    }
}
