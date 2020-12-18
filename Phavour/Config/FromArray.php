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
namespace Phavour\Config;

use Exception;

/**
 * FromArray
 */
class FromArray
{
    /**
     * @var string
     */
    protected $location = null;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @param string $fileLocation
     */
    public function __construct($fileLocation)
    {
        $this->location = $fileLocation;
    }

    /**
     * Get the array from the given location in construct
     * @throws Exception
     * @return array
     */
    public function getArray()
    {
        /** @noinspection PhpIncludeInspection */
        $config = @include $this->location;
        if (!is_array($config)) {
            throw new Exception('Config not found');
        }

        $this->config = $config;

        return $config;
    }

    /**
     * Return the config, only where the top level keys begin with $string
     * @param string $string
     * @return array
     * @throws Exception
     */
    public function getArrayWhereKeysBeginWith($string)
    {
        if (empty($this->config)) {
            $this->getArray();
        }
        $config = $this->config;

        $thisConfig = array();
        $cleanNameLength = mb_strlen($string);
        $string = mb_strtolower($string);
        foreach ($config as $key => $value) {
            $begin = substr($key, 0, $cleanNameLength);
            if (mb_strtolower($begin) == $string) {
                $thisConfig[$key] = $value;
            }
        }
        return $thisConfig;
    }
}