<?php

declare(strict_types=1);

namespace App;

use App\File\FileSystem;
use Godruoyi\Snowflake\Snowflake;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Webpatser\Uuid\Uuid;
use App\File\Cache;

class Utils
{
    /**
     * @return Uuid
     */
    public static function Uuid()
    {
        return Uuid::generate(4)->hex;
    }

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
     * @param \DOMNodeList|\DOMNode|\DOMNode[]|string|null $node A Node to use as the base for the crawling
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public static function crawler($node = null, string $uri = null, string $baseHref = null): Crawler
    {
        return new Crawler($node, $uri, $baseHref);
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
     * 雪花算法
     *
     * @param int $datacenter
     * @param int $workerid
     * @return \Godruoyi\Snowflake\Snowflake
     */
    public static function snow(Int $datacenter = null, Int $workerid = null): Snowflake
    {
        return new Snowflake($datacenter, $workerid);
    }

    /**
     * @param string $key
     * @return \App\Encrypter
     */
    public static function aes(String $key): \App\Encrypter
    {
        return Encrypter::getInstance($key);
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

        $sqlArr = array_filter(explode(';', file_get_contents($source)), fn ($item) => strpos($item, "`{$table}`") !== false, ARRAY_FILTER_USE_BOTH);

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
        return array_reduce(explode('.', $name), fn ($data, $key) => $data[$key] ?? $default, $data);
    }
}
