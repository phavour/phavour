<?php
/**
 * Phavour PHP Framework Library
 *
 * @author      Roger Thomas <roger.thomas@rogerethomas.com>
 * @copyright   2013 Roger Thomas
 * @link        http://www.rogerethomas.com
 * @license     http://www.rogerethomas.com/license
 * @since       1.0.0
 * @package     Phavour
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Phavour\Cache;

use Phavour\Cache\AdapterAbstract;
use Phavour\Helper\FileSystem\Directory;

/**
 * @author Roger Thomas
 * AdapterFileSystem
 */
class AdapterFileSystem extends AdapterAbstract
{
    /**
     * @var string
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * @var string
     */
    private $prefix = 'PCAFS_';

    /**
     * @var integer
     */
    const DEFAULT_TTL = 86400;

    /**
     * @var Directory|null
     */
    private $helper = null;

    /**
     * @var string|null
     */
    private $path = null;

    /**
     * Construct requires an array with the key of path, which should point
     * to a writable folder.
     * @param array $config
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        if (!array_key_exists('path', $config) || !is_dir($config['path']) || !is_writable($config['path'])) {
            throw new \Exception('"path" key must be specified and be a valid location');
        }
        $this->path = rtrim($config['path'], '/\\');
        $this->helper = new Directory();
    }

    /**
     * Get a value from cache
     * @return mixed|boolean false
     */
    public function get($key)
    {
        $md5 = md5($key);
        $folderPath = $this->getFolderPathFromMd5($md5);
        $path = $this->path . self::DS . $folderPath . self::DS . $this->prefix . $md5;
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }

        $content = file_get_contents($path);
        if (!$content) {
            return false;
        }

        $pieces = explode(PHP_EOL, $content);
        if (!is_array($pieces) || !is_numeric($pieces[0])) {
            unlink($path);
            return false;
        }

        $val = @unserialize($pieces[1]);
        if ($val === false) {
            $this->remove($key);
            return false;
        }

        return $val;
    }

    /**
     * Set a cache value
     * @param string $key
     * @param mixed $value
     * @param integer $ttl (optional) default 86400
     * @return boolean
     */
    public function set($key, $value, $ttl = self::DEFAULT_TTL)
    {
        $md5 = md5($key);
        $folderPath = $this->getFolderPathFromMd5($md5);
        $create = $this->helper->createPath($this->path, $folderPath);
        if (!$create) {
            return false;
        }
        $basePaths = $this->path . self::DS . $folderPath;
        $path = $basePaths . self::DS . $this->prefix . $md5;

        $handle = fopen($path, 'w');
        if ($handle) {
            $data = time() + $ttl . PHP_EOL . serialize($value);
            $success = fwrite($handle, $data);
            return ($success !== false);
        }

        return false;
    }

    /**
     * Check if a cached key exists.
     * This method calls get() so if you need a value, use get()
     * instead.
     *
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return ($this->get($key) !== false);
    }

    /**
     * Renew a cached item by key
     * @param string $key
     * @param integer $ttl (optional) default 86400
     * @return boolean
     */
    public function renew($key, $ttl = self::DEFAULT_TTL)
    {
        $md5 = md5($key);
        $folderPath = $this->getFolderPathFromMd5($md5);
        $path = $this->path . self::DS . $folderPath . self::DS . $this->prefix . $md5;
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }

        $content = file_get_contents($path);
        if (!$content) {
            return false;
        }

        $pieces = explode(PHP_EOL, $content);
        if (!is_array($pieces) || !is_numeric($pieces[0])) {
            unlink($path);
            return false;
        }

        $val = @unserialize($pieces[1]);
        if (!$val) {
            $this->remove($key);
            return false;
        }

        return $this->set($key, $val, $ttl);
    }

    /**
     * Remove a cached value by key.
     * @param string $key
     * @return boolean
     */
    public function remove($key)
    {
        $md5 = md5($key);
        $folderPath = $this->getFolderPathFromMd5($md5);
        $path = $this->path . self::DS . $folderPath . self::DS . $this->prefix . $md5;
        if (!file_exists($path) || !is_readable($path)) {
            return true;
        }

        return unlink($path);
    }

    /**
     * Flush all existing Cache.
     * @return boolean
     */
    public function flush()
    {
        try {
        	$this->helper->recursivelyDeleveFromDirectory($this->path);
        	return true;
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * Get a folder path from a given MD5
     * @param string $md5
     * @return string
     */
    private function getFolderPathFromMd5($md5)
    {
        $folderOne = substr($md5, 0, 2);
        $folderTwo = substr($md5, 2, 2);

        return $folderOne . self::DS . $folderTwo;
    }
}
