<?php
declare(strict_types=1);

namespace App;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as Reader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Writer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Exception;

/**
 * PHPExcel
 * 官网Api文档 https://phpoffice.github.io/PhpSpreadsheet/master/
 * githhub地址  https://github.com/PHPOffice/PhpSpreadsheet
 */
class Excel
{
    /**
     * 整理数组
     * 把二维数组 改造成 excel 格式的 一维数组
     */
    private function setData(array $data)
    {
        $excel = [];
        $j = 1;
        foreach ($data as $item) {
            $i = 65;
            foreach ($item as $value) {
                $excel[strtoupper(chr($i))."$j"] = $value;
                $i++;
            }
            $j++;
        }
        return $excel;
    }

    /**
     *  php的日期值是    1970-01-01   开始计算       单位：秒
     *  EXCEL        是      1900-01-01   开始计算       单位：天
     *  25569是EXCEL的1970-01-01代表的数字
     *
     * @param {number} time excel的时间
     * @param {string} format  格式化，不填就转化成时间戳
     *
     * @return {string} 时间
     */
    public function getExcelTime($time, $format = '')
    {
        if ($time > 25569) {
            $time = ($time - 25569) * 24 * 60 * 60;
            $time = $format ? date($format, $time) : $time;
        } else {
            $time ='';
        }

        return $time;
    }

    /**
     * 读取 Excel 文件
     * @param boolean   dataOnly 	仅读取数据
     * @param array 	sheetOnly 	仅阅读特定表格
     *
     * @return array
     */
    public function read($fileName, $dataOnly = true, $sheetsOnly = [])
    {
        try {
            if (stripos($fileName, '.xlsx') === false) {
                $fileName .= '.xlsx';
            }

            if (!file_exists($fileName)) {
                throw new Exception('文件不存在');
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        $reader = new Reader();

        $reader->setReadDataOnly($dataOnly);

        if ($sheetsOnly) {
            $reader->setLoadSheetsOnly($sheetsOnly);
        }

        return $reader->load($fileName)->getActiveSheet()->toArray();
    }

    /**
     * 写入 Excel 文件
     * @param boolean 	preCalculateFormulas			公式预先运算
     * @param boolean 	office2003Compatibility			Office 2003兼容包
     *
     * @return void
     */
    public function write(string $fileName, array $data, string $title = '', bool $preCalculateFormulas = false, bool $office2003Compatibility = false)
    {
        try {
            if (empty($data)) {
                throw new Exception('没有数据');
            }

            if (stripos($fileName, '.xlsx') === false) {
                $fileName .= '.xlsx';
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $title && $sheet->setTitle($title);

        // 写入每一个单元格
        $data = $this->setData($data);
        foreach ($data as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $writer = new Writer($spreadsheet);

        $writer->setPreCalculateFormulas($preCalculateFormulas);

        $writer->setOffice2003Compatibility($office2003Compatibility);

        return $writer->save($fileName);
    }
}
