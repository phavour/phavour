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
namespace Phavour\Application;

/**
 * Environment
 */
class Environment
{
    /**
     * @var string
     */
    protected $errorMode = 'production';

    /**
     * Construct the object, which in turn builds the methods required.
     */
    public function __construct()
    {
        $this->configureErrorMode();
    }

    /**
     * Is the application in production mode?
     * @return boolean
     */
    public function isProduction()
    {
        return ($this->errorMode === 'production' || (!$this->isTest() && !$this->isDevelopment()));
    }

    /**
     * Is the application in test mode?
     * @return boolean
     */
    public function isTest()
    {
        return ($this->errorMode === 'test');
    }

    /**
     * Is the application in development mode?
     * @return boolean
     */
    public function isDevelopment()
    {
        return ($this->errorMode === 'development');
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->errorMode;
    }

    /**
     * Establishes the Application Environment, by checking (in this order)
     *     1) getenv('APPLICATION_ENV')
     *     2) defined('APPLICATION_ENV')
     *
     * @return void
     */
    private function configureErrorMode()
    {
        if (false != ($errorMode = getenv('APPLICATION_ENV'))) {
            // @codeCoverageIgnoreStart
            $this->errorMode = strtolower($errorMode);
            return;
            // @codeCoverageIgnoreEnd
        }

        if (defined('APPLICATION_ENV')) {
            // @codeCoverageIgnoreStart
            $this->errorMode = strtolower(APPLICATION_ENV);
            return;
            // @codeCoverageIgnoreEnd
        }

        return;
    }
}