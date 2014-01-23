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
namespace Phavour\Middleware;

use Phavour\Http\Request;
use Phavour\Http\Response;

/**
 * \Phavour\Middleware\MiddlewareProcessor
 */
class MiddlewareProcessor
{
    /**
     * @var array
     */
    private $running = array();

    /**
     * @var array
     */
    private $middlewares = array();

    /**
     * Construct, passing the array of MiddlewareAbstracts
     * @param array $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Called by \Phavour\Application, this method runs all
     * Middleware onBefore methods
     * @return void
     */
    public function runBefore(Request $request, Response $response)
    {
        foreach ($this->middlewares as $middleware) {
            if (class_exists($middleware)) {
                $instance = new $middleware($request, $response);
                if ($instance instanceof MiddlewareAbstract) {
                    /* @var $instance MiddlewareAbstract */
                    $instance->onBefore();
                    $this->running[] = $instance;
                }
            }
        }
    }

    /**
     * Called by \Phavour\Application, this method runs all
     * Middleware onBefore methods
     * @return void
     */
    public function runAfter()
    {
        foreach ($this->running as $key => $middleware) {
            /* @var $middleware MiddlewareAbstract */
            $middleware->onAfter();
            unset($this->running[$key]);
        }

        return;
    }
}
