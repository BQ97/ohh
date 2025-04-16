<?php

declare(strict_types=1);

namespace App\File;

use ZipArchive;

class Zip
{
    public static function pack(string $path, ?string $pwd = null)
    {
        $fileName = pathinfo($path, PATHINFO_FILENAME);

        $zipDir = UPLOAD_PATH . date('Ymd') . DS;

        is_dir($zipDir) || mkdir($zipDir);

        $zipPath = $zipDir . $fileName . '.zip';

        $handler = new ZipArchive;

        if ($handler->open($zipPath, ZipArchive::CREATE) !== true) {
            return false;
        }

        if ($pwd) $handler->setPassword($pwd);

        if (is_dir($path)) {
            $handler->addEmptyDir($fileName);
            $files = fileSystem($path)->ls('/', true, FileSystem::LS_FILE_OPTION);
            foreach ($files as $f) {
                // 这里的 目录分隔符 不能使用 DS ，windows 和 linux 会有差异， 现在统一使用 /
                $entryname = $fileName . '/' . $f;
                $handler->addFile($path . DS . $f, $entryname);
                if ($pwd) $handler->setEncryptionName($entryname, ZipArchive::EM_AES_256, $pwd);
            }
        } else {
            if (!file_exists($path)) {
                return false;
            }

            $f = pathinfo($path, PATHINFO_BASENAME);
            $handler->addFile($path, $f);
            if ($pwd) $handler->setEncryptionName($f, ZipArchive::EM_AES_256, $pwd);
        }

        $handler->close();

        return $zipPath;
    }

    public static function unPack(string $path, ?string $pwd = null)
    {
        $handler = new ZipArchive;

        if ($handler->open($path) !== true) {
            return false;
        }

        $extractDir = UPLOAD_PATH . date('Ymd');

        is_dir($extractDir) || mkdir($extractDir);

        if ($pwd) $handler->setPassword($pwd);

        $handler->extractTo($extractDir);

        $handler->close();

        return $extractDir;
    }

    public static function getFiles(string $zip, ?string $pwd = null)
    {
        $handler = new ZipArchive;

        if ($handler->open($zip) !== true) {
            return false;
        }

        if ($pwd) $handler->setPassword($pwd);

        for ($i = 0; $i < $handler->count(); $i++) {
            yield $i => $handler->getNameIndex($i);
        }
    }

    public static function getContent(string $zip, string $name, ?string $pwd = null)
    {
        $handler = new ZipArchive;

        if ($handler->open($zip) !== true) {
            return false;
        }

        if ($pwd) $handler->setPassword($pwd);

        return $handler->getFromName($name);
    }

    public static function saveFileToLocal(string $zip, string $name, ?string $pwd = null)
    {
        $contents = static::getContent($zip, $name, $pwd);

        if (!$contents) {
            return false;
        }

        $md5File = md5($contents) . '.' . pathinfo($name, PATHINFO_EXTENSION);

        $Ymd = date('Ymd');

        $exportPath = UPLOAD_PATH . $Ymd;

        is_dir($exportPath) || mkdir($exportPath);

        file_put_contents($exportPath . DS . $md5File, $contents, LOCK_EX);

        return $exportPath . DS . $md5File;
    }
}
