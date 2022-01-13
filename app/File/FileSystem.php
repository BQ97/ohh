<?php

declare(strict_types=1);

namespace App\File;

use League\Flysystem\{
    FileAttributes,
    DirectoryAttributes,
    StorageAttributes,
    Local\LocalFilesystemAdapter,
    UnixVisibility\PortableVisibilityConverter,
    Filesystem as File
};

use Exception;

class FileSystem
{
    private $instances;

    /**
     * @var \League\Flysystem\Filesystem
     */
    private $handler;

    public function __construct(string $path = CACHE_PATH)
    {
        if (!is_dir($path)) {
            throw new Exception('目录不存在');
        }

        if (empty($this->instances[$path])) {
            // The internal adapter
            $adapter = new LocalFilesystemAdapter(
                // Determine the root directory
                $path,
                // Customize how visibility is converted to unix permissions
                PortableVisibilityConverter::fromArray([
                    'file' => [
                        'public' => 0640,
                        'private' => 0604,
                    ],
                    'dir' => [
                        'public' => 0740,
                        'private' => 7604,
                    ],
                ]),

                // Write flags
                LOCK_EX,
                // How to deal with links, either DISALLOW_LINKS or SKIP_LINKS
                // Disallowing them causes exceptions when encountered
                LocalFilesystemAdapter::DISALLOW_LINKS
            );

            // The FilesystemOperator
            $this->instances[$path] = new File($adapter);
        }

        $this->handler = $this->instances[$path];

        return true;
    }

    /**
     * @param string $path
     * @return self
     */
    public static function getInstance(string $path = CACHE_PATH): FileSystem
    {
        return new static($path);
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
        $this->handler->createDirectory($dirPath);

        return true;
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
    public function ls(string $dirPath, bool $recursive = false)
    {
        $listing = $this->handler->listContents($dirPath, $recursive);

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

        return ['d' => $dirs, 'f' => $files];
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
        $this->handler->copy($source, $dest);

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
        $this->handler->move($source, $dest);

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
        if ($result['d'] || $result['f']) {
            return false;
        }

        $this->handler->deleteDirectory($dirPath);

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
        $this->handler->deleteDirectory($dirPath);

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
        $this->handler->write($filePath, $content);

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
        $this->handler->delete($filePath);

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
    public function formatBytes(string $bytes, int $dec = 2)
    {

        $unitPow = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
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
    public function link($target, $link)
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return symlink($target, $link);
        }

        $mode = is_dir($target) ? 'J' : 'H';

        exec("mklink /{$mode} " . escapeshellarg($link) . ' ' . escapeshellarg($target));
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->handler, $name], $arguments);
    }
}
