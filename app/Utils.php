<?php

declare(strict_types=1);

namespace App;

use App\File\Cache;
use App\File\FileSystem;
use Cake\Chronos\Chronos;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Grafika\Grafika;
use Grafika\Gd\Image;
use Phar;

class Utils
{
    /**
     * 增加工作日，过滤掉周六周日
     * @param string $start  开始日期
     * @param int $workDay 增加几个工作日
     * @return string|bool
     */
    public static function addWorkDays(string $start, int $workDay = 1)
    {
        if (strtotime($start) === false) {
            return false;
        }

        $startTimeStamp = strtotime($start) + 86400;

        $week2 = [2, 1, 1, 1, 1, 1, 2];

        $w1 = (int)date('w', $startTimeStamp);

        $w2 = [...array_slice($week2, $w1), ...array_slice($week2, 0, $w1)];

        $weeks = floor($workDay / 7);

        $arr = [];

        if ($weeks) {
            for ($i = 0; $i < $weeks; $i++) {
                $arr = array_merge($arr, $w2);
            }
        }

        $after = ($workDay % 7);

        $afterWeeks = array_slice($w2, 0, $after);

        $result = [...$arr, ...$afterWeeks];

        $days = array_sum($result);

        $days = $days + $week2[$after];

        return date('Y-m-d', $startTimeStamp + 86400 * $days);
    }

    /**
     * @param int|Chronos $start
     * @param int|Chronos $end
     * @param bool $abs
     * @return int
     */
    public static function computeDistanceDay($start, $end, bool $abs = true)
    {
        $startObj = $start instanceof Chronos ? $start : Chronos::createFromTimestamp($start);
        $endObj = $end instanceof Chronos ? $end : Chronos::createFromTimestamp($end);

        return $startObj->diffInDays($endObj->addDays(1), $abs);
    }

    /**
     * 计算两个日期之间的间隔
     * @param int $start 开始时间时间戳
     * @param int $end  结束时间时间戳
     */
    public static function computeDistanceDate(int $start, int $end)
    {
        if ($start > $end) [$start, $end] = [$end, $start];

        $startObj = Chronos::createFromTimestamp($start);

        $day = $totalDays = static::computeDistanceDay($startObj, $end);

        $month = 0;

        while ($day >= ($t = $startObj->endOfMonth()->day)) {
            $day -= $t;
            $month += 1;
            $startObj = $startObj->addMonths(1);
        }

        return ['month' => $month, 'day' => $day, 'totalDays' => $totalDays];
    }

    /**
     * @param \DOMNodeList|\DOMNode|\DOMNode[]|string|null $node A Node to use as the base for the crawling
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public static function crawler($node = null, ?string $uri = null, ?string $baseHref = null): Crawler
    {
        return new Crawler(...func_get_args());
    }

    /**
     * @param array $config
     * @return \GuzzleHttp\Client
     */
    public static function httpClient(array $config = [])
    {
        return new Client($config);
    }

    /**
     * 文件缓存
     * @param string $prefix 缓存空间 默认 app
     * @return Cache
     */
    public static function cache(string $prefix = 'BoQing'): Cache
    {
        return Cache::getInstance($prefix);
    }

    /**
     * @param string $key
     * @return \App\Encrypter
     */
    public static function aes(string $key, string $cipher = 'aes-128-ecb'): \App\Encrypter
    {
        return new Encrypter($key, $cipher);
    }

    /**
     * @param string $key
     * @return \App\MyTree
     */
    public static function tree(array $config = []): MyTree
    {
        return MyTree::getInstance($config);
    }

    /**
     * @param string $path  目录  默认 缓存目录
     * @return \App\File\FileSystem
     */
    public static function fileSystem(string $path = CACHE_PATH): FileSystem
    {
        return FileSystem::getInstance($path);
    }

    /**
     * @param string $source
     * @param string $table
     *
     * @return string $path
     */
    public static function getTableByAllSql(string $source, string $table)
    {
        if (!file_exists($source)) {
            return false;
        }

        $sqlArr = array_filter(explode(';', file_get_contents($source)), fn($item) => strpos($item, "`{$table}`") !== false, ARRAY_FILTER_USE_BOTH);

        $tableSql = join(';', $sqlArr) . ';';

        static::fileSystem(UPLOAD_PATH)->write($fileName = date('Ymd') . DS . "{$table}.sql", $tableSql);

        return UPLOAD_PATH . $fileName;
    }

    /**
     * @param array $data
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public static function getData(array $data, string $name, $default = null)
    {
        return $name ? array_reduce(explode('.', $name), fn($data, $key) => $data[$key] ?? $default, $data) : $data;
    }

    public static function downloadResource(string $url)
    {
        $date = date('Ymd');

        static::fileSystem(UPLOAD_PATH)->mkDir($date);

        $path = UPLOAD_PATH . $date . DS .  pathinfo($url, PATHINFO_BASENAME);

        static::httpClient()->get($url, [
            'sink' => $path
        ]);

        return $path;
    }

    public static function jsonFormat(string | array | object $json): string
    {
        return json_encode(is_string($json) ? json_decode($json, true) : $json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public static function pharPack(string $filename, string $packDir, string $index)
    {
        $phar = new Phar($filename);

        $phar->buildFromDirectory($packDir);

        $phar->compressFiles(Phar::GZ);

        $phar->stopBuffering();

        return $phar->setStub($phar->createDefaultStub($index));
    }

    public static function zipPack(string $sourceDir, string $password = '')
    {
        return app('zip')->setPassword($password)->pack($sourceDir);
    }

    public static function zipUnPack(string $zip, string $password = '')
    {
        return app('zip')->setPassword($password)->unPack($zip);
    }

    public static function makePwdByMobile(string $mobile)
    {
        return md5('8888' . substr($mobile, -6) . '8888');
    }

    public static function generatePgsqlAutoIncrPk(string $table, int $current = 1, string $PK = 'id')
    {
        $sql = <<<PGSQL
        CREATE SEQUENCE "public"."{$table}_{$PK}_seq" 
        INCREMENT 1
        MINVALUE  1
        MAXVALUE 9223372036854775807
        START 1
        CACHE 1;
        SELECT setval('"public"."{$table}_{$PK}_seq"', {$current}, false);
        ALTER SEQUENCE "public"."{$table}_{$PK}_seq"
        OWNED BY "public"."{$table}"."{$PK}";
        ALTER SEQUENCE "public"."{$table}_{$PK}_seq" OWNER TO "root";
        ALTER TABLE "public"."{$table}" 
        ALTER COLUMN "{$PK}" SET DEFAULT nextval('{$table}_{$PK}_seq'::regclass);
        PGSQL;
        return $sql;
    }

    public static function countCodeLines(string $dir, array $exts = ['vue', 'ts', 'php', 'js'])
    {
        $fileSystem = fileSystem($dir);

        $files = $fileSystem->ls('/', true, 'f');

        $files = array_filter($files, fn($file) => in_array(pathinfo($file, PATHINFO_EXTENSION), $exts), ARRAY_FILTER_USE_BOTH);

        return array_sum(array_map(fn($file) => count(file($dir . DS . $file)), $files));
    }

    /**
     * @param int|Chronos|string $start
     * @param int|Chronos|string $end
     * @param bool $abs
     * @return \DateInterval
     */
    public static function date_diff($start, $end, bool $abs = false)
    {
        $startObj = is_int($start) ? Chronos::createFromTimestamp($start) : Chronos::parse($start);
        $endObj = is_int($end) ? Chronos::createFromTimestamp($end) : Chronos::parse($end);
        if ($startObj->getTimestamp() > $endObj->getTimestamp()) {
            [$startObj, $endObj] = [$endObj, $startObj];
        }

        return $startObj->diff($endObj->addDays(1), $abs);
    }

    public static function resizeImage(string $image, $newWidth = 800, $newHeight = 800)
    {
        if (!file_exists($image)) {
            return false;
        }

        $ext = pathinfo($image, PATHINFO_EXTENSION);

        if (!in_array($ext, ['jpg', 'png', 'jpeg'], true)) {
            return false;
        }

        $editor = Grafika::createEditor();
        $imageObj = Image::createFromFile($image);

        $width  = $imageObj->getWidth();
        $height = $imageObj->getHeight();
        $ratio  = $width / $height;

        // Try basing it on width first
        $resizeWidth  = $newWidth;
        $resizeHeight = round($newWidth / $ratio);

        if (($resizeWidth > $newWidth) or ($resizeHeight > $newHeight)) { // Oops, either with or height does not fit
            // So base on height instead
            $resizeHeight = $newHeight;
            $resizeWidth  = round($newHeight * $ratio);
        }

        $editor->resizeExact($imageObj, $resizeWidth, $resizeHeight);

        $editor->save($imageObj, $image);

        $editor->free($imageObj);

        return true;
    }

    public static function pdf2Image(string $path, string $imageExt = 'png')
    {
        $outputPath = UPLOAD_PATH . date('Ymd') . DS;
        is_dir($outputPath) || mkdir($outputPath, 0755, true);

        // 创建 Imagick 对象
        $im = new \Imagick();
        $im->readImageBlob(file_get_contents($path));

        // 获取页数
        $numberOfPages = $im->getNumberImages();

        $zip = new \ZipArchive;

        $zip->open($outputPath . pathinfo($path, PATHINFO_FILENAME) . '.zip', \ZipArchive::CREATE);

        // 将每一页的 PDF 转为图片
        for ($i = 0; $i < $numberOfPages; $i++) {
            $im->setIteratorIndex($i);
            $page = $im->getImage();
            $page->setImageFormat($imageExt); // 图像格式可以根据需要更改
            $zip->addFromString("{$i}.{$imageExt}", $page->getImageBlob());
            $page->clear();
        }

        $zip->close();
        // 释放 Imagick 对象
        $im->clear();

        return true;
    }
}
