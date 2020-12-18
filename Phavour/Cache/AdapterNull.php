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
 * AdapterNull
 */
class AdapterNull extends AdapterAbstract
{
    /**
     * Dummy method
     * @param array $config (optional)
     */
    public function __construct(array $config = array())
    {
    }

    /**
     * Dummy method
     * @param string $key
     * @return boolean false
     */
    public function get($key)
    {
        return false;
    }

    /**
     * Dummy method
     * @param string $key
     * @param mixed $value
     * @param integer $ttl (optional) default 86400
     * @return boolean false
     */
    public function set($key, $value, $ttl = 86400)
    {
        return false;
    }

    /**
     * Dummy method
     *
     * @param string $key
     * @return boolean false
     */
    public function has($key)
    {
        return false;
    }

    /**
     * Dummy method
     * @param string $key
     * @param integer $ttl (optional) default 86400
     * @return boolean false
     */
    public function renew($key, $ttl = 86400)
    {
        return false;
    }

    /**
     * Dummy method
     * @param string $key
     * @return boolean false
     */
    public function remove($key)
    {
        return false;
    }

    /**
     * Dummy method
     * @return boolean false
     */
    public function flush()
    {
        return false;
    }
}
