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
namespace Phavour\Tests\Http;

use Exception;
use Phavour\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * ResponseTest
 */
class ResponseTest extends TestCase
{
    /**
     * @var Response
     */
    private $response = null;

    public function setUp(): void
    {
        $this->response = new Response();
    }

    public function testSetHeaders()
    {
        $array = array('X-Name' => 'Joe', 'X-Age' => '30');
        $this->response->setHeaders($array);

        $this->assertCount(2, $this->response->getHeaders());
    }

    public function testSetOverrideHeaders()
    {
        $array = array('X-Name' => 'Joe', 'X-Age' => '30');
        $this->response->setHeaders($array, false);

        $this->assertCount(2, $this->response->getHeaders());
    }

    /**
     * @throws Exception
     */
    public function testSendHeaders()
    {
        $array = array('X-Name' => 'Joe', 'X-Age' => '30');
        $this->response->setHeaders($array, false);
        $this->response->sendHeaders();
        $this->assertCount(2, $this->response->getHeaders());
    }

    public function testSendDirectRedirect()
    {
        $this->response->setStatus(301);
        try {
            $this->response->sendHeaders();
        } catch (Exception $e) {
            $this->assertStringContainsString('cannot send a redirect using a regular response', $e->getMessage());
            return;
        }
        $this->fail('expected exception');
    }

    /**
     * @throws Exception
     */
    public function testResponseRedirect()
    {
        $this->response->cleanHeaders();
        $this->response->redirect('/', 302);
        $this->assertArrayHasKey('Location', $this->response->getHeaders());
        $this->response->cleanHeaders();
    }

    /**
     * @throws Exception
     */
    public function testSendInvalidStatus()
    {
        $this->response->setStatus(999);
        $this->response->sendHeaders();
        $this->assertEquals(500, $this->response->getStatus());
    }

    public function testSendInvalidRedirectStatus()
    {
        try {
            $this->response->redirect('/', 999);
        } catch (Exception $e) {
            $this->assertEquals('Invalid redirect status specified', $e->getMessage());
            return;
        }
        $this->fail('expected exception');
    }

    public function testSetHeader()
    {
        $this->response->setHeaders(array(), true);
        $array = array('X-Name' => 'Joe', 'X-Age' => '30');
        foreach ($array as $k => $v) {
            $this->response->setHeader($k, $v);
        }
        $array = array('X-Name' => 'Joe', 'X-Age' => '30', 'X-ApiKey' => 1234);
        foreach ($array as $k => $v) {
            $this->response->setHeader($k, $v, false);
        }

        $this->assertCount(3, $this->response->getHeaders());
    }

    public function testSetBody()
    {
        $this->response->setBody('abc');
        $this->assertEquals('abc', $this->response->getBody());
    }

    public function testSetStatus()
    {
        $this->response->setStatus(404);
        $this->assertEquals(404, $this->response->getStatus());
    }
}
