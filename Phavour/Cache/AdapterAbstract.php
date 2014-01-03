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

/**
 * AdapterAbstract
 */
abstract class AdapterAbstract
{
    /**
     * Construct, giving the configuration
     * @param array $config
     */
    abstract function __construct(array $config);

    /**
     * Get a cached value by key
     * @param unknown $key
     * @return mixed|boolean false
     */
    abstract function get($key);

    /**
     * Set a cache value
     * @param string $key
     * @param mixed $value
     * @param integer $ttl
     * @return boolean
     */
    abstract function set($key, $value, $ttl);

    /**
     * Check if a cached key exists.
     * If you require a returned value, call get($key) instead.
     * @param string $key
     * @return boolean
     */
    abstract function has($key);

    /**
     * Renew a cached item by key
     * @param string $key
     * @param integer $ttl
     * @return boolean
     */
    abstract function renew($key, $ttl);

    /**
     * Remove a cached value by key.
     * @param string $key
     * @return boolean
     */
    abstract function remove($key);

    /**
     * Flush all existing Cache
     * @return boolean
     */
    abstract function flush();
}
