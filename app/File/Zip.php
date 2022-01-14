<?php

declare(strict_types=1);

namespace App\File;

use ZipArchive;
use App\Utils;

class Zip
{
    /**
     * @var ZipArchive
     */
    private $handler;

    private $password = '12345678';

    public function __construct()
    {
        $this->handler = new ZipArchive;
    }

    /**
     * @param string $password
     * @return Zip
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return ZipArchive
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param string $filename
     * @param int $flags
     * 
     * @return Zip
     */
    public function open(string $filename, int $flags = ZipArchive::CHECKCONS)
    {
        if ($this->handler->open($filename, $flags) === true) {
            $this->handler->setPassword($this->password);
            return $this;
        }

        return false;
    }

    /**
     * @param string $sourceDir
     * @param string $name
     * 
     * @return string
     */
    public function pack(string $sourceDir, string $name)
    {
        $files = FileSystem::getInstance($sourceDir)->ls('/', true)['f'];

        $name = pathinfo($name ?: Utils::Uuid(), PATHINFO_FILENAME) . '.zip';

        $destDir = UPLOAD_PATH . date('Ymd') . DS;

        FileSystem::getInstance(UPLOAD_PATH)->mkdir(date('Ymd'));

        $this->open($destDir . $name, ZipArchive::CREATE);

        foreach ($files as $path) {
            $this->handler->addFile($sourceDir . DS . $path, $path);
            $this->handler->setEncryptionName($path, ZipArchive::EM_AES_256, $this->password);
        }

        return $destDir . $name;
    }

    /**
     * @param string $zip
     * 
     * @return string
     */
    public function unPack(string $zip)
    {
        $this->open($zip);

        $dest = UPLOAD_PATH . date('Ymd') . DS . Utils::Uuid() . DS;

        $this->handler->extractTo($dest);

        return $dest;
    }

    /**
     * @param string $zip
     * 
     * @return \Generator
     */
    public function getFiles(string $zip)
    {
        $this->open($zip);

        for ($i = 0; $i < $this->handler->count(); $i++) {
            yield $i => $this->handler->getNameIndex($i);
        }
    }

    /**
     * @param string $zip
     * @param string $name
     * 
     * @return string
     */
    public function getContent(string $zip, string $name)
    {
        $this->open($zip);

        return $this->handler->getFromName($name);
    }

    /**
     * @param string $zip
     * @param string $name
     * @param string $password
     * 
     * @return string
     */
    public function saveFileToLocal(string $zip, string $name)
    {
        $fileName = date('Ymd') . DS . $name;

        FileSystem::getInstance(UPLOAD_PATH)->write($fileName, $this->getContent($zip, $name));

        return UPLOAD_PATH . $fileName;
    }

    public function __destruct()
    {
        $this->handler->close();
    }
}
