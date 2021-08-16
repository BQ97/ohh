<?php
declare(strict_types=1);

namespace App;

/**
 * @desc：php aes加密解密类
 */
class Aes
{
    /**
     * @var string 加密方式
     */
    protected $cipher = 'aes-128-ecb';

    /**
     * @var string 加密KEY
     */
    protected $key;

    /**
     * @param string $key Configuration parameter
     */
    public function __construct(String $key)
    {
        $this->key = $key;
    }

    /**
     * 加密
     * @param string $data Input data
     * @return string
     */
    public function encrypt(String $data)
    {
        return bin2hex(openssl_encrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA));
    }

    /**
     * 解密
     *
     * @param string $data Encrypted data
     * @return string
     */
    public function decrypt(String $data)
    {
        return openssl_decrypt(hex2bin($data), $this->cipher, $this->key, OPENSSL_RAW_DATA);
    }

    public function __debugInfo()
    {
        $data = get_object_vars($this);
        unset($data['key']);
        return $data;
    }
}
