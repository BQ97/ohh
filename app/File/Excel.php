<?php

declare(strict_types=1);

namespace App\File;

use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Spreadsheet,
    Cell\Coordinate,
    Worksheet\Drawing
};
use Closure;

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
    public static function read(string $fileName, ?string $sheet = null, $nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false)
    {
        $spreadsheet = IOFactory::load($fileName);

        $worksheet = $spreadsheet->getActiveSheet();

        if ($sheet) {

            if (!$spreadsheet->sheetNameExists($sheet)) {
                return [];
            }

            $worksheet = $spreadsheet->getSheetByName($sheet);
        }

        $data = $worksheet->toArray($nullValue, $calculateFormulas, $formatData, $returnCellRef);

        $drawingCollection = $worksheet->getDrawingCollection();

        $fileSystem = FileSystem::getInstance(UPLOAD_PATH);
        $fileSystem->mkDir(date('Ymd'));

        foreach ($drawingCollection as $drawing) {
            [$column, $row] = Coordinate::indexesFromString($drawing->getCoordinates());
            $index = $column - 1;

            if ($drawing instanceof Drawing) {
                $path = UPLOAD_PATH . date('Ymd') . DS . atom_next_id() . '.' . $drawing->getExtension();

                switch ($drawing->getExtension()) {
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg(imagecreatefromjpeg($drawing->getPath()), $path);
                        break;
                    case 'gif':
                        imagegif(imagecreatefromgif($drawing->getPath()), $path);
                        break;
                    case 'png':
                        imagepng(imagecreatefrompng($drawing->getPath()), $path);
                        break;
                }
                $data[$row][$index] = $path;
            }
        }

        return $data;
    }

    /**
     * 写入 Excel 文件
     * @param array[] 	$options  配置
     * @param string 	$fileName  文件名字
     *
     * @return string
     */
    public static function write(array $options, ?string $fileName = null)
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

    /**
     * @param array data 必须是干干净净的 二维数组
     * @param ?string|array|callable column_key
     * @param ?string index_key
     * @param ?callable $where
     * @param ?int $start_row
     * @return array
     */
    public static function column(
        array $data,
        string|array|callable|null $column_key = null,
        ?string $index_key = null,
        ?callable $where = null,
        ?int $start_row = null
    ) {
        if (is_callable($column_key)) {
            $column_key = Closure::fromCallable($column_key);
        } else if (is_array($column_key)) {
            $column_key = array_map([static::class, 'columnIndexFromString'], $column_key);
        } else {
            if (!$column_key) $column_key = '*';
            if ($column_key === '*') {
                $column_key = array_keys($data[0]);
            } else {
                $column_key = static::columnIndexFromString($column_key);
            }
        }

        $result = [];

        if ($index_key) $index_key = static::columnIndexFromString($index_key);

        foreach ($data as $key => $value) {
            if ($start_row && $key < $start_row) {
                continue;
            }

            if ($where && !call_user_func($where, $value, $key)) {
                continue;
            }

            $resultItem = [];

            if ($column_key instanceof Closure) {
                $resultItem = call_user_func($column_key, $value, $key);
            } elseif (is_array($column_key)) {
                $resultItem = array_map(fn ($c) => $value[$c], $column_key);
            } else {
                $resultItem = $value[$column_key];
            }

            if (isset($index_key)) {
                $result[$value[$index_key]][] = $resultItem;
            } else {
                $result[] = $resultItem;
            }
        }

        return $result;
    }

    /**
     * @param string $columnAddress
     * @return int — Column index (A = 0)
     */
    public static function columnIndexFromString(string $columnAddress): int
    {
        return Coordinate::columnIndexFromString($columnAddress) - 1;
    }
}
