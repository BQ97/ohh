<?php

declare(strict_types=1);

namespace App;

use Exception;
use Webpatser\Uuid\Uuid;

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

    public static function getData(array $data, string $name, $default = null)
    {
        foreach (array_filter(explode('.', $name)) as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }

        return $data;
    }
}
