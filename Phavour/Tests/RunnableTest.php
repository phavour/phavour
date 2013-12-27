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
namespace Phavour\Tests;

use Phavour\Tests\testdata\ClassExample;
use Phavour\Http\Request;
use Phavour\Http\Response;
use Phavour\Runnable\View;
use Phavour\Application\Environment;
use Phavour\Router;

/**
 * @author Roger Thomas
 * RunnableTest
 */
class RunnableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassExample
     */
    private $runnable = null;

    public function setUp()
    {
        $request = new Request();
        $response = new Response();
        $view = new View('test', 'test', 'none');
        $env = new Environment();
        $router = new Router();
        $this->runnable = new ClassExample($request, $response, $view, $env, null, $router);
    }

    public function testInit()
    {
        $this->assertTrue($this->runnable->init());
    }

    public function testIsConstructed()
    {
        $this->assertTrue($this->runnable->isConstructed());
    }

    public function testGetCacheAdapter()
    {
        $this->assertInstanceOf('\Phavour\Cache\AdapterNull', $this->runnable->getCache());
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf('\Phavour\Http\Request', $this->runnable->getRequest());
    }

    public function testGetResponse()
    {
        $this->assertInstanceOf('\Phavour\Http\Response', $this->runnable->getResponse());
    }

    public function testGetView()
    {
        $this->assertInstanceOf('\Phavour\Runnable\View', $this->runnable->getView());
    }

    public function testRedirect()
    {
        $this->runnable->redirect('/', 302);
    }

    public function testGetUrl()
    {
        $this->assertEquals('abc.123', $this->runnable->urlFor('abc.123'));
        $request = new Request();
        $response = new Response();
        $view = new View('test', 'test', 'none');
        $env = new Environment();
        $testEmpty = new ClassExample($request, $response, $view, $env, null, null);
        $this->assertEmpty($testEmpty->urlFor('abc.123'));
    }
}