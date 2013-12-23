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

/**
 * @author Roger Thomas
 * AdapterApc
 */
class AdapterApc extends AdapterAbstract
{
    /**
     * @var boolean
     */
    private $hasApc = false;

    /**
     * @var integer
     */
    const DEFAULT_TTL = 86400;

    /**
     * Construct the adapter. The parameter is not required.
     * @param array $config (not needed) required by Abstract
     */
    public function __construct(array $config = array())
    {
        $this->checkExtension();
    }

    /**
     * Get a value from cache
     * @return mixed|boolean false
     */
    public function get($key)
    {
        if (!$this->hasApc) {
            return false;
        }

        $record = apc_fetch($key, $found);
        if ($found) {
            return $record;
        }

        return false;
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
        if (!$this->hasapc) {
            return false;
        }

        return apc_store($key, $value, $ttl);
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
        if (!$this->hasApc) {
            return false;
        }

        $val = apc_fetch($key, $fetched);

        if ($fetched) {
            return apc_store($key, $val, $ttl);
        }

        return false;
    }

    /**
     * Remove a cached value by key.
     * @param string $key
     * @return boolean
     */
    public function remove($key)
    {
        if (!$this->hasApc) {
            return false;
        }

        return apc_delete($key);
    }

    /**
     * Flush all existing Cache
     * @return boolean
     */
    public function flush()
    {
        if (!$this->hasApc) {
            return false;
        }

        return apc_clear_cache();
    }

    /**
     * Check if apc is enabled
     * @return boolean
     */
    private function checkExtension()
    {
        $this->hasApc = (extension_loaded('apc'));
        return $this->hasApc;
    }
}
