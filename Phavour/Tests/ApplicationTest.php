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

use Phavour\Application;
use Phavour\Cache\AdapterNull;
use PHPUnit\Framework\TestCase;

/**
 * ApplicationTest
 */
class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    private $app = null;

    /**
     * @var \Phavour\Package[]
     */
    private $packages = array();

    public function setUp(): void
    {
        /** @var \Phavour\Package[] $appPackages */
        global $appPackages;
        $this->packages = $appPackages;

        $this->app = new Application(APP_BASE, $this->packages);
        $this->app->setCacheAdapter(new AdapterNull());
    }

    public function testCantUseCacheFirst()
    {
        @ob_start();
        $app = new Application(APP_BASE, $this->packages);
        $app->setup();
        $app->setCacheAdapter(new AdapterNull());
        $content = @ob_get_clean();
        $this->assertStringContainsString('500: Unexpected Error', $content);
    }

    public function testSetup()
    {
        $this->app->setup();
        $this->assertTrue($this->app->isSetup());
    }

    public function testInvalidRoute()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = '/this/isnt/a/valid/path/ever';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        @ob_start();
        $result = $this->app->run();
        $content = @ob_get_clean();
        $this->assertStringContainsString('404: Page Not Found', $content);
    }

    public function testDirectRenderRoute()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = '/view-only';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        @ob_start();
        $result = $this->app->run();
        $content = @ob_get_clean();
        $this->assertStringContainsString('Test view file, for using view.directRender', $content);
        $this->assertStringContainsString('Test view file, declared view.layout', $content);
    }

    public function testValidRoute()
    {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        @ob_start();
        $result = $this->app->run();
        $content = @ob_get_clean();
        $this->assertStringContainsString('Welcome to Phavour', $content);
    }

    public function testHasMiddlewareAndMiddlewareIsCalled()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = '/middleware';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        @ob_start();
        $result = $this->app->run();
        $content = @ob_get_clean();
        $this->assertTrue($this->app->hasMiddleware());
        $this->assertEquals('foobar', $content);
    }

    public function testInvalidPackage()
    {
        try {
            $this->app->getPackage('foobar');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Phavour\Application\Exception\PackageNotFoundException', $e);
            return;
        }
        $this->fail('expected exception');
    }
}
