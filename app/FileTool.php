<?php

declare(strict_types=1);

namespace App;

use App\Application;

/**
 * @method static boolean makeDir(string $dirPath) 创建目录
 * @method static array readDir(string $dirPath, $recursive = false) 获取目录内文件
 * @method static boolean copyDir(string $source, string $dest) 将一个文件夹内容复制到另一个文件夹
 * @method static boolean moveDir(string $source, string $dest) 移动文件夹, 相当于WIN下的ctr+x(剪切操作)
 * @method static boolean deleteDir(string $dirPath) 删除文件夹
 * @method static boolean clearDir(string $dirPath) 递归清空文件夹内的文件及子目录
 * @method static boolean writeFile(string $filePath, string $content) 文件写操作
 * @method static boolean copyFile(string $sourceFile, string $destFile) 文件复制
 * @method static boolean moveFile(string $sourceFile, string $destFile) 文件重命名或移动文件
 * @method static boolean deleteFile(string $filePath) 删除文件
 * @method static string formatBytes(string $bytes, int $dec = 2) 字节格式化 把字节数格式为 B K M G T 描述的大小
 */
class FileTool
{
    /**
     * 文件系统代理
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([Application::getInstance()->fileSystem, $name], $arguments);
    }
}
