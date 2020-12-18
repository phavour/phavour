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
namespace Phavour\Autoload;

use Composer\Autoload\ClassLoader;
use RuntimeException;

/**
 * Caches the autoloader using APC
 */
class ApcClassLoader
{
    /**
     * The real autoloader
     *
     * @var ClassLoader
     */
    private $loader;

    /**
     * Cache prefix
     *
     * @var string
     */
    private $key;

    /**
     * @param string $key
     * @param ClassLoader $loader
     *
     * @throws RuntimeException
     */
    public function __construct($key, ClassLoader $loader)
    {
        if (!extension_loaded('apc')) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('APC is not enabled.');
            // @codeCoverageIgnoreEnd
        }

        $this->key = $key;
        $this->loader = $loader;
    }

    /**
     * Register the APC autoloader
     *
     * @param boolean $prepend
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    /**
     * Unregister the APC autoloader
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * @param string $class
     * @return boolean|null
     */
    public function loadClass($class)
    {
        $file = $this->findFile($class);

        if ($file) {
            /** @noinspection PhpIncludeInspection */
            require $file;

            return true;
        }

        return null;
    }

    /**
     * Finds the file from the cache or the autoloader
     *
     * @param string $class
     * @return false|string
     */
    public function findFile($class)
    {
        $file = apc_fetch($this->key . $class);

        if (!$file) {
            $file = $this->loader->findFile($class);
            apc_store($this->key . $class, $file);
        }

        return $file;
    }

    /**
     * Proxy other methods to the actual autoloader
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args)
    {
        return call_user_func_array(array($this->loader, $method), $args);
    }
}
