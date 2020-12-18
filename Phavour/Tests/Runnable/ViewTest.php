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
namespace Phavour\Tests\Runnable;

use Exception;
use Phavour\Application;
use Phavour\Application\Exception\PackageNotFoundException;
use Phavour\Http\Response;
use Phavour\Router;
use Phavour\Runnable\View;
use Phavour\Runnable\View\Exception\LayoutFileNotFoundException;
use Phavour\Runnable\View\Exception\ViewFileNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * ViewTest
 */
class ViewTest extends TestCase
{
    /**
     * @var Application|null
     */
    private $app = null;

    /**
     * @var View
     */
    private $view = null;

    public function setUp(): void
    {
        global $appPackages;

        $this->app = new Application(APP_BASE, $appPackages);
        $this->app->setup();

        $this->view = new View($this->app, 'DefaultPackage', 'Index', 'index');
        $this->view->setConfig(
            array(
                'foo' => 'bar',
                'abc' => '123'
            )
        );
    }

    public function testPackageName()
    {
        $this->view->setPackage('None');
        $this->assertEquals('NonePackage', $this->view->getPackage());
        $this->view->setPackage('NonePackage');
        $this->assertEquals('NonePackage', $this->view->getPackage());
    }

    public function testConfig()
    {
        $this->assertCount(2, $this->view->getConfig());
        $this->assertEquals('bar', $this->view->config('foo'));
        $this->assertNull($this->view->config('invalidKey'));
    }

    public function testClassName()
    {
        $this->assertEquals('Index', $this->view->getClass());
        $this->view->setClass('Abc123');
        $this->assertEquals('Abc123', $this->view->getClass());
    }

    public function testMethodName()
    {
        $this->assertEquals('index', $this->view->getMethod());
        $this->view->setScriptName('noSuchMethod');
        $this->assertEquals('noSuchMethod', $this->view->getMethod());
        $this->assertEquals('noSuchMethod', $this->view->getScriptName());
        $this->view->setMethod('noSuchMethodAlt');
        $this->assertEquals('noSuchMethodAlt', $this->view->getMethod());
        $this->assertEquals('noSuchMethodAlt', $this->view->getScriptName());
    }

    public function testMagicMethods()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->abc = '123';
        $this->assertEquals('123', $this->view->abc);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertNull($this->view->noSuchVariable);
        $this->view->set('name', 'joe');
        $this->assertEquals('joe', $this->view->get('name'));
    }

    public function testGetSetMethods()
    {
        $this->view->set('name', 'joe');
        $this->assertEquals('joe', $this->view->get('name'));
        $this->assertNull($this->view->get('age'));
    }


    public function testEnableDisabled()
    {
        $this->view->enableView();
        $this->assertTrue($this->view->isEnabled());
        $this->view->disableView();
        $this->assertFalse($this->view->isEnabled());
    }

    /**
     * @throws View\Exception\ViewFileNotFoundException
     * @throws PackageNotFoundException
     */
    public function testRender()
    {
        $this->view = new View($this->app, 'TestPackage', 'UserTest', 'name');
        $this->view->disableView();
        $this->assertTrue($this->view->render());
    }

    /**
     * @throws ViewFileNotFoundException
     * @throws PackageNotFoundException
     */
    public function testRenderContains()
    {
        $this->view->setResponse(new Response());
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->data = 'joe';
        @ob_start();
        $this->view->render('index', 'Index', 'DefaultPackage');
        $result = @ob_get_clean();
        $this->assertStringContainsString('joe', $result);
    }

    /**
     * @throws PackageNotFoundException
     * @throws ViewFileNotFoundException
     * @throws LayoutFileNotFoundException
     */
    public function testLayoutContains()
    {
        $this->view->setResponse(new Response());
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->data = '123';
        @ob_start();
        $this->view->setLayout('DefaultPackage::default');
        $this->view->render('index', 'Index', 'DefaultPackage');
        $result = @ob_get_clean();
        $this->assertStringContainsString('123', $result);
    }

    /**
     * @throws PackageNotFoundException
     * @throws ViewFileNotFoundException
     * @throws LayoutFileNotFoundException
     */
    public function testLayoutNoPackage()
    {
        $this->view->setResponse(new Response());
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->data = 'abc';
        @ob_start();
        $this->view->setLayout('default');
        $this->view->render('index', 'Index', 'DefaultPackage');
        $result = @ob_get_clean();
        $this->assertStringContainsString('abc', $result);
    }

    public function testRenderInvalid()
    {
        $this->view->setResponse(new Response());
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->name = 'abc';
        try {
            $this->view->render('namae', 'UserTest', 'DefaultPackage');
        } catch (Exception $e) {
            $this->assertInstanceOf('\Phavour\Runnable\View\Exception\ViewFileNotFoundException', $e);
            $this->assertStringContainsString('Invalid view file', $e->getMessage());
            return;
        }
        $this->fail('expected exception');
    }

    public function testRenderInvalidLayout()
    {
        $this->view->setResponse(new Response());
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->name = 'abc';
        try {
            $this->view->setLayout('nosuchlayout');
            $this->view->render('namae', 'UserTest', 'TestPackage');
        } catch (Exception $e) {
            $this->assertInstanceOf('\Phavour\Runnable\View\Exception\LayoutFileNotFoundException', $e);
            $this->assertStringContainsString('Invalid layout file path', $e->getMessage());
            return;
        }
        $this->fail('expected exception');
    }

    public function testGetUrl()
    {
        $this->assertEmpty($this->view->urlFor('abc.123'));
        $this->view->setRouter(new Router());
        $this->assertEquals('abc.123', $this->view->urlFor('abc.123'));
    }
}
