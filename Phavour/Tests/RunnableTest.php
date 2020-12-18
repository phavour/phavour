<?php /** @noinspection PhpIllegalPsrClassPathInspection */
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
namespace Phavour\Tests;

use Exception;
use Phavour\Application;
use Phavour\Application\Environment;
use Phavour\Http\Request;
use Phavour\Http\Response;
use Phavour\Router;
use Phavour\Runnable\View;
use Phavour\Tests\testdata\ClassExample;
use PHPUnit\Framework\TestCase;

/**
 * RunnableTest
 */
class RunnableTest extends TestCase
{
    /**
     * @var Application|null
     */
    private $app = null;

    /**
     * @var ClassExample
     */
    private $runnable = null;

    public function setUp(): void
    {
        $this->app = $this->getMockBuilder('Phavour\\Application')->disableOriginalConstructor()->getMock();

        $request = new Request();
        $response = new Response();
        $view = new View($this->app, 'test', 'test', 'none');
        $env = new Environment();
        $router = new Router();
        $this->runnable = new ClassExample($request, $response, $view, $env, null, $router, array('foo' => 'bar'));
    }

    public function testInit()
    {
        $this->assertTrue($this->runnable->init());
    }

    public function testIsConstructed()
    {
        $this->assertTrue($this->runnable->isConstructed());
    }

    public function testGetConfig()
    {
        $this->assertTrue(is_array($this->runnable->getConfig()));
    }

    public function testGetConfigKeyWhichDoesNotExist()
    {
        $this->assertNull($this->runnable->config('foobar'));
    }

    public function testConfigKeyEqualsExpectedBar()
    {
        $this->assertEquals('bar', $this->runnable->config('foo'));
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

    /**
     * @throws Exception
     */
    public function testRedirect()
    {
        $this->runnable->redirect('/', 302);
        $this->assertEquals(302, $this->runnable->getResponse()->getStatus());
    }

    public function testGetUrl()
    {
        $this->assertEquals('abc.123', $this->runnable->urlFor('abc.123'));
        $request = new Request();
        $response = new Response();
        $view = new View($this->app, 'test', 'test', 'none');
        $env = new Environment();
        $testEmpty = new ClassExample($request, $response, $view, $env, null, null);
        $this->assertEmpty($testEmpty->urlFor('abc.123'));
    }

    public function testNotFoundRoute()
    {
        $this->runnable->notFound();
        $this->assertEquals(404, $this->runnable->getResponse()->getStatus());
    }
}
