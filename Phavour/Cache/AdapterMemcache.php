<?php
/**
 * Phavour PHP Framework Library
 *
 * @author      Phavour Project
 * @copyright   2013-2014 Phavour Project
 * @link        http://phavour-project.com
 * @license     http://phavour-project.com/license
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
 * AdapterMemcache
 */
class AdapterMemcache extends AdapterAbstract
{
    /**
     * @var integer
     */
    const DEFAULT_TTL = 86400;

    /**
     * @var \Memcache
     */
    private $memcache = null;

    /**
     * Construct the adapter, giving an array of servers.
     * @example
     *     array(
     *         array(
     *             'host' => 'cache1.example.com',
     *             'port' => 11211,
     *             'weight' => 1,
     *             'timeout' => 60
     *         ),
     *         array(
     *             'host' => 'cache2.example.com',
     *             'port' => 11211,
     *             'weight' => 2,
     *             'timeout' => 60
     *         )
     *     )
     * @param array $servers
     */
    public function __construct(array $servers)
    {
        try {
            $this->memcache = new \Memcache();
            foreach ($servers as $server) {
                $this->memcache->addserver(
                    $server['host'],
                    $server['port'],
                    null,
                    $server['weight'],
                    $server['timeout']
                );
            }
        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            $this->memcache = null;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Get a value from cache
     * @param string $key
     * @return mixed|boolean false
     */
    public function get($key)
    {
        if (!$this->hasConnection()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        try {
            return $this->memcache->get($key);
        // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
        }

        return false;
        // @codeCoverageIgnoreEnd
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
        if (!$this->hasConnection()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $flag = null;

        if (!is_bool($value) && !is_int($value) && !is_float($value)) {
            $flag = MEMCACHE_COMPRESSED;
        }

        try {
            return $this->memcache->set($key, $value, $flag, $ttl);
        // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
        }

        return false;
        // @codeCoverageIgnoreEnd
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
        if (!$this->hasConnection()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $value = $this->get($key);
        if ($value) {
            $flag = null;
            if (!is_bool($value) && !is_int($value) && !is_float($value)) {
                $flag = MEMCACHE_COMPRESSED;
            }

            try {
                return $this->memcache->replace($key, $value, $flag, $ttl);
            // @codeCoverageIgnoreStart
            } catch (\Exception $e) {
            }
        }
        // @codeCoverageIgnoreEnd

        return false;
    }

    /**
     * Remove a cached value by key.
     * @param string $key
     * @return boolean
     */
    public function remove($key)
    {
        if (!$this->hasConnection()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        try {
            return $this->memcache->delete($key);
        // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
        }

        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Flush all existing Cache
     * @return boolean
     */
    public function flush()
    {
        if (!$this->hasConnection()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        try {
            return $this->memcache->flush();
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
        }

        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Check if instance of \Memcache has been assigned
     * @return boolean
     */
    private function hasConnection()
    {
        return ($this->memcache instanceof \Memcache);
    }
}
