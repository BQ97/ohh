<?php

declare(strict_types=1);

namespace App\File;

use ArrayAccess;
use ArrayIterator;
use Exception;
use IteratorAggregate;
use Psr\SimpleCache\CacheInterface;

class Cache implements ArrayAccess, CacheInterface, IteratorAggregate
{
    /**
     * @var static[]
     */
    private static array $instances = [];

    /**
     * @var \App\File\FileSystem
     */
    private FileSystem $fileSystem;

    private function __construct(private string $prefix)
    {
        $this->fileSystem = FileSystem::getInstance(CACHE_PATH);

        $this->fileSystem->mkDir($prefix);
    }

    /**
     * @param string $prefix 缓存目录
     * @return Cache
     */
    public static function getInstance(string $prefix = 'BoQing'): Cache
    {
        if (empty($prefix)) {
            throw new Exception('缓存目录不能为空');
        }

        if (empty(static::$instances[$prefix])) {

            static::$instances[$prefix] = new static($prefix);
        }

        return static::$instances[$prefix];
    }

    /**
     * 获取文件路径
     * @param string $file key
     * @return string
     */
    private function getFilePath(string $file): string
    {
        return $this->prefix . DS . $file . '.php';
    }

    /**
     * 获取文件配置
     * @param string $file
     * @return array
     */
    private function getConfig(string $file): array
    {
        $filename = CACHE_PATH . $this->getFilePath($file);

        if (file_exists($filename)) {

            return Loader::loadFile($filename);
        }

        return ['expire' => false, 'config' => null];
    }

    /**
     * 写入文件
     * @param string $key  key
     * @param string|array $value 数据
     * @param int $expire 过期时间
     * @return bool
     */
    private function write(string $key, $value, int $expire = 0): bool
    {
        $filePath = $this->getFilePath($key);

        $expire = $expire > 0 ? $expire + time() : 0;

        $content = '<?php return ' . var_export(['config' => serialize($value), 'expire' => $expire], true) . ';';

        return $this->fileSystem->write($filePath, $content);
    }

    /**
     * @param string $key
     * @param string|array|mixed $default
     * @return string|array|mixed
     */
    public function get($key, $default = null): mixed
    {
        if ($this->has($key)) {
            $data = $this->getConfig($key);

            return unserialize($data['config']);
        }

        return $default;
    }

    /**
     * 获取当前空间下所有配置
     * @return array
     */
    public function all(): array
    {
        $files = $this->fileSystem->ls($this->prefix, false, FileSystem::LS_FILE_OPTION);

        if (!$files) {
            return [];
        }

        return array_reduce($files, function ($config, $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);

            $config[$key] = $this->get($key);

            return $config;
        }, []);
    }

    /**
     * 获取过期时间
     * @param string $key
     * @return int|bool
     */
    public function getExpire(string $key)
    {
        $data = $this->getConfig($key);

        if ($data['expire'] === false) {
            return false;
        }

        if ($data['expire'] === 0) {
            return 0;
        }

        $expire = $data['expire'] - time();
        if ($expire > 0) {
            return $expire;
        }

        $this->delete($key);

        return false;
    }

    /**
     * 设置过期时间
     * @param string $key
     * @param int $expire  0 永久储存
     * @return int|bool
     */
    public function setExpire(string $key, int $expire)
    {
        if (!$this->has($key) || $expire < 0) {
            return false;
        }

        return $this->set($key, $this->get($key), $expire);
    }

    /**
     * 存储数据
     * @param string $file  key
     * @param string|array $value 数据
     * @param int $expire 过期时间 0 永久储存
     * @return int|bool
     */
    public function set($key, $value, $expire = 0): bool
    {
        return $this->write($key, $value, $expire);
    }

    /**
     * 判断缓存是否存在
     * @access public
     * @param  string $name 缓存变量名
     * @return bool
     */
    public function has($name): bool
    {
        return false === $this->getExpire($name) ? false : true;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     */
    public function inc(string $name, int $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) + $step;
            $expire = $this->getExpire($name);
        } else {
            $value  = $step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     */
    public function dec(string $name, int $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) - $step;
            $expire = $this->getExpire($name);
        } else {
            $value  = -$step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }

    /**
     * 清空当前目录下所有数据
     * @return bool
     */
    public function clear(): bool
    {
        return $this->fileSystem->rmRf($this->prefix);
    }

    /**
     * 删除数据
     * @param string $file  key
     * @return bool
     */
    public function delete($key): bool
    {
        return $this->fileSystem->rm($this->getFilePath($key));
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    public function __unset($name)
    {
        return $this->delete($name);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->delete($offset);
    }

    public function getIterator(): \Traversable
    {
        return new ArrayIterator($this->all());
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    public function setMultiple($values, $ttl = 0): bool
    {
        foreach ($values as $key => $value) $this->set($key, $value, $ttl);

        return true;
    }

    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) $this->delete($key);

        return true;
    }
}
