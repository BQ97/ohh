<?php

declare(strict_types=1);

namespace App;

use Exception;

/**
 * @desc：php aes加密解密类
 */
class Encrypter
{
    /**
     * @var string 加密方式
     */
    private string $cipher;

    /**
     * @var string 加密KEY
     */
    private string $key;

    /**
     * @param string $key 
     * @param string $cipher
     */
    public function __construct(string $key, string $cipher = 'aes-128-ecb')
    {
        $this->setKey($key)->setCipher($cipher);
    }

    public function setKey(string $key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return static
     * @throws Exception
     */
    public function setCipher(string $cipher)
    {
        if (in_array($cipher, openssl_get_cipher_methods())) {
            $this->cipher = $cipher;
            return $this;
        }

        throw new Exception('openssl_encrypt(): Unknown cipher algorithm');
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
}
