<?php

declare(strict_types=1);

namespace App;

/**
 * @desc：php aes加密解密类
 */
class Encrypter
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

    public static function getInstance(String $key)
    {
        return new static($key);
    }

    /**
     * 加密
     * @param string $data Input data
     * @return string
     */
    public function encrypt(String $data)
    {
        return base64_encode(openssl_encrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA));
    }

    /**
     * 解密
     *
     * @param string $data Encrypted data
     * @return string
     */
    public function decrypt(String $data)
    {
        return openssl_decrypt(base64_decode($data, true), $this->cipher, $this->key, OPENSSL_RAW_DATA);
    }

    public function __debugInfo()
    {
        $data = get_object_vars($this);
        unset($data['key']);
        return $data;
    }
}
