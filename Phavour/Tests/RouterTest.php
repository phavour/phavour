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
namespace Phavour\Tests;

use Phavour\Router;

/**
 * RouterTest
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $routes = array();

    /**
     * @var Router|null
     */
    private $router = null;

    public function setUp()
    {
        $this->routes = array(
            'index' => array(
                'method' => 'GET',
                'path' => '/',
            ),
            'missing_path' => array(
                'method' => 'WONTWORK' // Coverage for return false on missing 'path' key check
            ),
            'missing_method' => array(
                'path' => '/' // Coverage for return false on missing 'method' key check
            ),
            'index_name_not_same' => array(
                'method' => 'POST|PUT|DELETE',
                'path' => '/somewhere/{name}',
            ),
            'index_name' => array(
                'method' => 'GET',
                'path' => '/user/{name}',
            ),
            'index_post' => array(
                'method' => 'POST|PUT|DELETE',
                'path' => '/',
            ),
            'index_postputdelete_name' => array(
                'method' => 'POST|PUT|DELETE',
                'path' => '/user/{name}',
            ),
            'index_allowed_ip' => array(
                'method' => 'GET',
                'path' => '/auth/ip/allow',
                'allow' => array(
                    'from' => '127.0.0.1'
                )
            ),
            'index_view_only_not_valid' => array(
                'method' => 'GET',
                'path' => '/view-only/not-valid',
                'view.directRender' => new \stdClass(),
            ),
            'index_layout_not_valid' => array(
                'method' => 'GET',
                'path' => '/layout/not-valid',
                'view.directRender' => true,
                'view.layout' => new \stdClass(),
            ),
        );

        $this->router = new Router();
        $this->router->setRoutes($this->routes);
    }

    public function testViewDirectRenderWhenNonBoolThrowsException()
    {
        $this->router->setPath('/view-only/not-valid');
        $this->router->setMethod('GET');
        try {
            $this->router->getRoute();
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Route for path not found.');
            return;
        }

        $this->fail('Failed asserting Exception for invalid route.');
    }

    public function testInvalidLayoutWhenDirectRenderIsUsedButNonBoolThrowsException()
    {
        $this->router->setPath('/layout/not-valid');
        $this->router->setMethod('GET');
        try {
            $this->router->getRoute();
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Route for path not found.');
            return;
        }

        $this->fail('Failed asserting Exception for invalid route.');
    }

    public function testGettersAndSettersWork()
    {
        $this->router->setMethod('GET');
        $this->router->setPath('/');
        $this->router->setRoutes($this->routes);
        $this->router->setIp('127.0.0.1');
        $this->assertEquals('GET', $this->router->getMethod());
        $this->assertEquals('/', $this->router->getPath());
        $this->assertEquals($this->routes, $this->router->getRoutes());
        $this->assertEquals('127.0.0.1', $this->router->getIp());
    }

    public function testDirectMatch()
    {
        $this->router->setMethod('GET');
        $this->router->setPath('/');
        $match = $this->router->getRoute();
        $this->assertEquals($match['path'], $this->routes['index']['path']);
        $this->assertEquals($match['method'], $this->routes['index']['method']);
    }

    public function testParamsReturn()
    {
        $this->router->setMethod('GET');
        $this->router->setPath('/');
        $match = $this->router->getRoute();
        $this->assertArrayHasKey('params', $match);
    }

    public function testParameterisedMatch()
    {
        $this->router->setMethod('GET');
        $this->router->setPath('/user/joe');
        $match = $this->router->getRoute();
        $this->assertEquals($match['path'], $this->routes['index_name']['path']);
        $this->assertEquals($match['method'], $this->routes['index_name']['method']);
        $this->assertEquals($match['params']['name'], 'joe');
    }

    public function testParameterisedMatchUrlDecoded()
    {
        $this->router->setMethod('GET');
        $this->router->setPath('/user/joe+bloggs');
        $match = $this->router->getRoute();
        $this->assertEquals($match['params']['name'], 'joe bloggs');

        $this->router->setPath('/user/joe%20bloggs');
        $match = $this->router->getRoute();
        $this->assertEquals($match['params']['name'], 'joe bloggs');
    }

    public function testOtherMethodMatch()
    {
        $methods = array('POST', 'PUT', 'DELETE');
        $names = array('Matthew', 'Mark', 'Luke');
        foreach ($methods as $methodKey => $method) {
            $this->router->setMethod($method);
            $this->router->setPath('/user/' . $names[$methodKey]);
            $match = $this->router->getRoute();
            $this->assertEquals($match['path'], $this->routes['index_postputdelete_name']['path']);
            $this->assertEquals($match['method'], $this->routes['index_postputdelete_name']['method']);
            $this->assertEquals($match['params']['name'], $names[$methodKey]);
        }
    }

    public function testExpectedException()
    {
        $this->router->setPath('/this/doesnt/exist');
        $this->router->setMethod('GET');
        try {
            $this->router->getRoute();
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Route for path not found.');
            return;
        }

        $this->fail('Failed asserting Exception for invalid route.');
    }

    public function testRouteAllowedByIp()
    {
        $this->router->setPath('/auth/ip/allow');
        $this->router->setMethod('GET');
        $this->router->setIp('127.0.0.1');
        $route = $this->router->getRoute();
        $this->assertEquals('127.0.0.1', $route['allow']['from']);
    }

    public function testRouteNotAllowedByIp()
    {
        $this->router->setPath('/auth/ip/allow');
        $this->router->setMethod('GET');
        $this->router->setIp('111.111.111.111');
        try {
            $this->router->getRoute();
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Route for path not found.');
            return;
        }

        $this->fail('Failed asserting Exception for invalid route.');
    }

    public function testUrlFor()
    {
        $this->assertEquals('/somewhere/{name}', $this->router->urlFor('index_name_not_same'));
        $this->assertEquals('/somewhere/foobar', $this->router->urlFor('index_name_not_same', array('name' => 'foobar')));
        // next test covers the 'temp' stored urls.
        $this->assertEquals('/somewhere/foobar', $this->router->urlFor('index_name_not_same', array('name' => 'foobar')));
        $this->assertEquals('missing_path', $this->router->urlFor('missing_path'));
    }
}
