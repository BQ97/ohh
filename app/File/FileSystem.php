<?php

declare(strict_types=1);

namespace App\File;

use League\Flysystem\{
    FileAttributes,
    DirectoryAttributes,
    StorageAttributes,
    Local\LocalFilesystemAdapter,
    Filesystem as File
};

use Exception;

/**
 * @method bool fileExists(string $location)
 * @method bool directoryExists(string $location)
 * @method bool has(string $location)
 * @method string read(string $location)
 * @method resource readStream(string $location)
 * @method int lastModified(string $path)
 */
class FileSystem
{
    /**
     * @var array<string,File>
     */
    private static array $handlers;

    const LS_DIR_OPTION = 'd';

    const LS_FILE_OPTION = 'f';

    public function __construct(private string $path = CACHE_PATH)
    {
        if (!is_dir($path)) {
            throw new Exception('目录不存在');
        }

        if (empty(static::$handlers[$path])) {
            static::$handlers[$path] = new File(new LocalFilesystemAdapter($path));
        }
    }

    /**
     * @param string $path
     * @return self
     */
    public static function getInstance(string $path = CACHE_PATH): FileSystem
    {
        return new static($path);
    }

    public function getHandle(): File
    {
        return static::$handlers[$this->path];
    }

    /**
     * @access public
     *
     * @param string $dirPath 目标目录
     *
     * @return boolean
     */
    public function mkDir(string $dirPath)
    {
        $this->getHandle()->createDirectory($dirPath);

        return true;
    }

    public static function makeUploadYmdDir()
    {
        $Ymd = date('Ymd');

        $dir = UPLOAD_PATH . $Ymd;

        is_dir($dir) || mkdir($dir, 0755, true);

        return $dir;
    }

    /**
     * 获取目录内文件
     *
     * @access public
     *
     * @param string $dirPath 所要读取内容的目录名
     * @param bool $recursive 是否递归读取
     *
     * @return array
     */
    public function ls(string $dirPath, bool $recursive = false, ?string $option = null)
    {
        $listing = $this->getHandle()->listContents($dirPath, $recursive);

        $files  = [];
        $dirs = [];

        /**
         * @var StorageAttributes $item
         */
        foreach ($listing as $item) {
            $path = $item->path();

            if (in_array($path, ['.', '..'])) {
                continue;
            }

            if ($item instanceof DirectoryAttributes) {
                $dirs[] = $path;
            } elseif ($item instanceof FileAttributes) {
                $files[] = $path;
            }
        }

        switch ($option) {
            case static::LS_DIR_OPTION:
                return $dirs;

            case static::LS_FILE_OPTION:
                return $files;

            default:
                # 默认所有
                return [static::LS_DIR_OPTION => $dirs, static::LS_FILE_OPTION => $files];
        }
    }

    /**
     * 将一个文件夹内容复制到另一个文件夹
     *
     * @access public
     *
     * @param string $source 被复制的文件夹名
     * @param string $dest 所要复制文件的目标文件夹
     *
     * @return boolean
     */
    public function cp(string $source, string $dest)
    {
        $this->getHandle()->copy($source, $dest);

        return true;
    }

    /**
     * 移动文件夹, 相当于WIN下的ctr+x(剪切操作)
     *
     * @access public
     *
     * @param string $source 原目录名
     * @param string $dest 目标目录
     *
     * @return boolean
     */
    public function mv(string $source, string $dest)
    {
        $this->getHandle()->move($source, $dest);

        return true;
    }

    /**
     * 删除文件夹
     *
     * @access public
     *
     * @param string $dirPath 所要删除文件的路径
     *
     * @return boolean
     */
    public function rmDir(string $dirPath)
    {
        $result = $this->ls($dirPath);
        if ($result[static::LS_DIR_OPTION] || $result[static::LS_FILE_OPTION]) {
            return false;
        }

        $this->getHandle()->deleteDirectory($dirPath);

        return true;
    }

    /**
     * 递归清空文件夹内的文件及子目录
     *
     * @access public
     *
     * @param string $dirPath 所要清空内容的文件夹名称
     *
     * @return boolean
     */
    public function rmRf(string $dirPath)
    {
        $this->getHandle()->deleteDirectory($dirPath);

        return true;
    }

    /**
     * 文件写操作
     *
     * @access public
     *
     * @param string $filePath 文件路径
     * @param string $content 文件内容
     *
     * @return boolean|int
     */
    public function write(string $filePath, string $content)
    {
        $this->getHandle()->write($filePath, $content);

        return true;
    }

    /**
     * 删除文件
     *
     * @access public
     *
     * @param string $filePath 文件路径
     *
     * @return boolean
     */
    public function rm(string $filePath)
    {
        $this->getHandle()->delete($filePath);

        return true;
    }

    /**
     * 字节格式化 把字节数格式为 B K M G T 描述的大小
     *
     * @access public
     *
     * @param integer $bytes 文件大小
     * @param integer $dec 小数点后的位数
     *
     * @return string
     */
    public static function formatBytes(string $bytes, int $dec = 2)
    {
        $unitPow = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $pos = 0;
        while ($bytes >= 1024) {
            $bytes /= 1024;
            $pos++;
        }

        return round($bytes, $dec) . ' ' . $unitPow[$pos];
    }

    /**
     * Create a symlink to the target file or directory. On Windows, a hard link is created if the target is a file.
     *
     * @param  string  $target
     * @param  string  $link
     * @return void
     */
    public static function link(string $target, string $link)
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return symlink($target, $link);
        }

        $mode = is_dir($target) ? 'J' : 'H';

        exec("mklink /{$mode} " . escapeshellarg($link) . ' ' . escapeshellarg($target));
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getHandle(), $name], $arguments);
    }
}
