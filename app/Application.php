<?php

declare(strict_types=1);

namespace App;

/**
 * Class Application.
 *
 * 应用核心
 * @property \Medoo\Medoo           $db
 * @property \App\Encrypter         $aes
 * @property \App\Request           $request
 * @property \App\File\Excel        $excel
 * @property \App\File\Xls          $xls
 * @property \App\File\Word         $word
 * @property \App\Model             $model
 * @property \App\Utils             $utils
 * @property \Mpdf\Mpdf             $mpdf
 * @property \App\MyTree            $tree
 * @property \App\Env               $env
 * @property \App\Xml               $xml
 * @property \App\File\FileSystem   $fileSystem
 * @property \App\File\Cache        $cache
 * @property \Faker\Generator       $faker
 * @property \Psy\Shell             $shell
 * @property \App\Hash              $hash
 * @property \GuzzleHttp\Client     $httpClient
 * @property \League\Plates\Engine $templates
 * @property \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
 * @property \Godruoyi\Snowflake\Snowflake $Snowflake
 * @property \App\Bitwise           $bitwise
 */
class Application extends Container
{
    public function __construct()
    {
        $this->make('db', [[
            'type'      => $this->env->get('DB_CONNECTION', 'mysql'),
            'database'  => $this->env->get('DB_DATABASE', ''),
            'host'      => $this->env->get('DB_HOST', 'localhost'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'port'      => $this->env->get('DB_PORT', 3306),
            'prefix'    => $this->env->get('DB_PRIFIX', ''),
            'username'  => $this->env->get('DB_USERNAME', 'root'),
            'password'  => $this->env->get('DB_PASSWORD', ''),
            'option'    => [
                \PDO::ATTR_STRINGIFY_FETCHES => false,
                \PDO::ATTR_EMULATE_PREPARES => false
            ],
            'logging' => true
        ]]);

        $this->templates->setDirectory(VIEW_PATH)->setFileExtension('phtml');

        $this->bindTo('faker', \Faker\Factory::create('zh_CN'));
    }

    /**
     * @param array config
     * @return MyTree
     */
    public function tree(array $config): \App\MyTree
    {
        return $this->make('tree', [$config], true);
    }

    /**
     * GuzzleHttp
     * @param array $option
     * @return \GuzzleHttp\Client
     */
    public function httpClient(array $option = []): \GuzzleHttp\Client
    {
        return $this->make('httpClient', [$option], true);
    }

    /**
     * 网页爬虫
     *
     * @param string data
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function crawler(String $data): \Symfony\Component\DomCrawler\Crawler
    {
        return $this->make('crawler', [$data], true);
    }

    /**
     * 获取model
     * @param string $name 表名
     * @param string $pk  主键
     *
     * @return \App\Model
     */
    public function model(String $name, String $pk = 'id'): \App\Model
    {
        return $this->model->setTable($name)->setPk($pk);
    }

    /**
     * 文件缓存
     * @param string $prefix 缓存空间 默认 app
     * @return \App\Cache
     */
    public function cache(string $prefix = 'BoQing'): \App\File\Cache
    {
        return \App\File\Cache::getInstance($prefix);
    }

    /**
     * 雪花算法
     *
     * @param int $datacenter
     * @param int $workerid
     * @return \Godruoyi\Snowflake\Snowflake
     */
    public function snow(Int $datacenter = null, Int $workerid = null): \Godruoyi\Snowflake\Snowflake
    {
        return $this->make('Snowflake', [$datacenter, $workerid], true);
    }

    /**
     * @param array $array
     * @return \App\ArrayObject
     */
    public function array(array $array = []): \App\Proxy\Arr
    {
        return $this->make('arr', [$array], true);
    }

    /**
     * @param array $array
     * @return \App\StringObject
     */
    public function string(String $string = ''): \App\Proxy\Str
    {
        return $this->make('str', [$string], true);
    }

    /**
     * @param object object
     * @return \App\ObjectProxy
     */
    public function object($object): \App\Proxy\Obj
    {
        return $this->make('obj', [$object], true);
    }

    /**
     * @param string $key
     * @return \App\Aes
     */
    public function aes(String $key): \App\Encrypter
    {
        return $this->make('aes', [$key], true);
    }

    /**
     * Create a new template and render it.
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    public function render($name, array $data = array())
    {
        echo $this->templates->render($name, $data);
    }

    /**
     * @param string $path  目录  默认 缓存目录
     * @return \App\File\FileSystem
     */
    public function fileSystem($path = CACHE_PATH): \App\File\FileSystem
    {
        return $this->make('fileSystem', [$path], true);
    }

    public function __debugInfo()
    {
        return [
            'app_name' => $this->env->get('APP_NAME'),
            'version' => '1.0.0',
            'date' => date('Y-m-d H:i:s'),
            'container' => parent::__debugInfo(),
        ];
    }
}
