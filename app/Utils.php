<?php

declare(strict_types=1);

namespace App;

use App\File\FileSystem;
use Exception;
use Godruoyi\Snowflake\Snowflake;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Webpatser\Uuid\Uuid;
use ZipArchive;

class Utils
{
    /**
     * 网络请求
     * @param  	string   $url  	请求地址
     * @param  	array    $param 请求参数
     * @return 	array
     */
    public static function httpRequest(string $url, array $param = [], bool $isPost = true)
    {
        $urlParam = http_build_query($param);
        $jsonParam = json_encode($param);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonParam);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonParam)
                )
            );
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            //     'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            //     'Content-Length: ' . strlen($param)
            // ));
        } else {
            if ($param) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $urlParam);
            }
            curl_setopt($ch, CURLOPT_HEADER, false);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }

    public static function request(String $method, String $url, array $content = [], array $header = [])
    {
        if (!in_array($method, ['GET', 'POST'], true)) {
            throw new Exception('$method只能取"GET"和"POST"');
        }

        $headers = "Content-Type:application/x-www-form-urlencoded\r\n";
        foreach ($header as $name => $val) {
            $headers .= $name . (!is_null($val) ? ':' . $val : '') . "\r\n";
        }

        return file_get_contents($url, false, stream_context_create([
            'http' => [
                'method'  => $method,
                'header'  => $headers,
                'content' => http_build_query($content)
            ]
        ]));
    }

    /**
     * 生成唯一优惠券
     * @param int $no_of_codes//定义一个int类型的参数 用来确定生成多少个优惠码
     * @param array $exclude_codes_array//定义一个exclude_codes_array类型的数组
     * @param int $code_length //定义一个code_length的参数来确定优惠码的长度
     * @return array//返回唯一的优惠券
     */
    public static function generate_promotion_code(int $no_of_codes = 1, array $exclude_codes_array = [], int $code_length = 6)
    {
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $promotion_codes = array(); //这个数组用来接收生成的优惠码
        for ($j = 0; $j < $no_of_codes; $j++) {
            $code = "";
            for ($i = 0; $i < $code_length; $i++) {
                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            //如果生成的4位随机数不再我们定义的$promotion_codes函数里面
            if (!in_array($code, $promotion_codes)) {
                if (is_array($exclude_codes_array)) {
                    if (!in_array($code, $exclude_codes_array)) { //排除已经使用的优惠码
                        $promotion_codes[$j] = $code; //将生成的新优惠码赋值给promotion_codes数组
                    } else {
                        $j--;
                    }
                } else {
                    $promotion_codes[$j] = $code; //将优惠码赋值给数组
                }
            } else {
                $j--;
            }
        }
        return $promotion_codes;
    }

    /**
     * 将图片 在浏览器上直接显示
     * @param {string}  img 	图片名称
     * @return void
     */
    public static function showImg(string $img)
    {
        $info = getimagesize($img);

        //获取文件后缀
        $imgExt = image_type_to_extension($info[2], false);
        $fun = 'imagecreatefrom' . $imgExt;

        //1.由文件或 URL 创建一个新图象。如:imagecreatefrompng ( string $filename )
        $imgInfo = $fun($img);

        //获取图片的 MIME 类型
        $mime = image_type_to_mime_type(exif_imagetype($img));
        header('Content-Type:' . $mime);

        //输出质量,JPEG格式(0-100),PNG格式(0-9)
        $quality = 100;
        if ($imgExt == 'png') {
            $quality = 9;
        }

        //2.将图像输出到浏览器或文件。如: imagepng ( resource $image )
        $getImgInfo = 'image' . $imgExt;
        $getImgInfo($imgInfo, null, $quality);
        imagedestroy($imgInfo);
    }

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
     */
    public static function crawler($node = null, string $uri = null, string $baseHref = null)
    {
        return new Crawler($node, $uri, $baseHref);
    }

    public static function httpClient(array $config = [])
    {
        return new Client($config);
    }

    /**
     * 雪花算法
     *
     * @param int $datacenter
     * @param int $workerid
     * @return \Godruoyi\Snowflake\Snowflake
     */
    public static function snow(Int $datacenter = null, Int $workerid = null): \Godruoyi\Snowflake\Snowflake
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
    public static function tree(array $config = []): \App\MyTree
    {
        return MyTree::getInstance($config);
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

        $fileName = date('Ymd') . DS . "{$table}.sql";

        FileSystem::getInstance(UPLOAD_PATH)->write($fileName, $tableSql);

        return UPLOAD_PATH . $fileName;
    }

    /**
     * @param string $sourceDir
     * @param string $zip
     * @param string $password
     *
     * @return bool|string
     */
    public static function zip(string $sourceDir, string $zip = null, string $password = '12345678')
    {
        $files = FileSystem::getInstance($sourceDir)->ls('/', true, FileSystem::LS_FILE_OPTION);

        $zipname = pathinfo($zip ?: static::Uuid(), PATHINFO_FILENAME) . '.zip';

        $destDir = UPLOAD_PATH . date('Ymd') . DS;

        FileSystem::getInstance(UPLOAD_PATH)->mkdir(date('Ymd'));

        $ZipArchive = new ZipArchive;

        if ($ZipArchive->open($destDir . $zipname, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $path) {
                $ZipArchive->addFile($sourceDir . DS . $path, $path);
                $ZipArchive->setEncryptionName($path, ZipArchive::EM_AES_256, $password);
            }

            $ZipArchive->close();

            return $destDir . $zipname;
        }

        return false;
    }

    /**
     * @param string $zip
     *
     * @return bool|string
     */
    public static function unzip(string $zip, string $password = '12345678')
    {
        $ZipArchive = new ZipArchive;

        if ($ZipArchive->open($zip) === TRUE) {

            $dest = UPLOAD_PATH . date('Ymd') . DS . static::Uuid() . DS;

            $ZipArchive->setPassword($password);

            $ZipArchive->extractTo($dest);

            $ZipArchive->close();

            return $dest;
        }

        return false;
    }

    /**
     * @param string $zip
     *
     * @return \Generator|bool
     */
    public static function getZipFiles(string $zip, string $password = '12345678')
    {
        $ZipArchive = new ZipArchive;

        if ($ZipArchive->open($zip) === TRUE) {
            $ZipArchive->setPassword($password);
            for ($i = 0; $i < $ZipArchive->count(); $i++) {
                yield $i => $ZipArchive->getNameIndex($i);
            }

            $ZipArchive->close();
        }

        return false;
    }

    /**
     * @param string $zip
     * @param string $name
     * @param string $password
     *
     * @return string|bool
     */
    public static function getZipContent(string $zip, string $name, string $password = '12345678')
    {
        $ZipArchive = new ZipArchive;

        if ($ZipArchive->open($zip) === TRUE) {
            $ZipArchive->setPassword($password);
            $content = $ZipArchive->getFromName($name);
            $ZipArchive->close();
            return $content;
        }

        return false;
    }

    /**
     * @param string $zip
     * @param string $name
     * @param string $password
     *
     * @return string
     */
    public static function saveZipFileToLocal(string $zip, string $name, string $password = '12345678')
    {
        $content = static::getZipContent($zip, $name, $password);

        if ($content) {
            $fileName = date('Ymd') . DS . $name;

            FileSystem::getInstance(UPLOAD_PATH)->write($fileName, $content);

            return UPLOAD_PATH . $fileName;
        }

        return false;
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
        if (!$name) {
            return $data;
        }

        return array_reduce(explode('.', $name), function ($data, $key) use ($default) {
            return isset($data[$key]) ? $data[$key] : $default;
        }, $data);
    }
}
