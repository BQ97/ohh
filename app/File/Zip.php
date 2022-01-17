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

    /**
     * @var string
     */
    private $password = '12345678';

    /**
     * @var string
     */
    private $exportPath;

    public function __construct()
    {
        $this->handler = new ZipArchive;

        $date = date('Ymd');

        $this->exportPath = UPLOAD_PATH . $date . DS;

        FileSystem::getInstance(UPLOAD_PATH)->mkDir($date);
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
    public function open(string $filename, int $flags = null)
    {
        if (call_user_func_array([$this->handler, 'open'], func_get_args()) === true) {
            $this->handler->setPassword($this->password);
            return $this;
        }

        return false;
    }

    /**
     * @param string $sourceDir
     *
     * @return string
     */
    public function pack(string $sourceDir)
    {
        $files = FileSystem::getInstance($sourceDir)->ls('/', true, FileSystem::LS_FILE_OPTION);

        $file = $this->exportPath . Utils::Uuid() . '.zip';

        array_reduce($files, function (ZipArchive $zip, string $path) use ($sourceDir) {

            $zip->addFile($sourceDir . DS . $path, $path);
            $zip->setEncryptionName($path, ZipArchive::EM_AES_256, $this->password);

            return $zip;
        }, $this->open($file, ZipArchive::CREATE)->getHandler());

        return $file;
    }

    /**
     * @param string $zip
     *
     * @return string
     */
    public function unPack(string $zip)
    {
        $path = $this->exportPath . Utils::Uuid() . DS;

        $this->open($zip)->getHandler()->extractTo($path);

        return $path;
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
        return $this->open($zip)->getHandler()->getFromName($name);
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
        FileSystem::getInstance($this->exportPath)->write($name, $this->getContent($zip, $name));

        return $this->exportPath . $name;
    }

    public function __destruct()
    {
        $this->handler->filename && $this->handler->close();
    }
}
