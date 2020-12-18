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
namespace Phavour\Tests\Auth;

use Exception;
use Phavour\Auth;
use Phavour\Auth\Service;
use Phavour\Tests\Auth\Adapter\TestCaseAdapter;
use Phavour\Tests\SessionTestBase;

class ServiceTest extends SessionTestBase
{
    /**
     * @throws Exception
     */
    public function testServiceValidCredentials()
    {
        $this->initLoggedOutSession();
        $adapter = new TestCaseAdapter();
        $adapter->setCredentials('test@example.com', 'password');
        $service = new Service($adapter);
        $result = $service->login();
        $this->assertTrue($result);
        $auth = Auth::getInstance();
        $identity = $auth->getIdentity();
        $roles = $auth->getRoles();
        $this->assertCount(2, $identity);
        $this->assertCount(2, $roles);
        $found = false;
        foreach ($roles as $role) {
            if ($role === 'admin') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testServiceInvalidCredentials()
    {
        $this->initLoggedOutSession();
        $adapter = new TestCaseAdapter();
        $adapter->setCredentials('joe@example.com', 'not-joe-password');
        $service = new Service($adapter);
        try {
            $service->login();
        } catch (Exception $e) {
            $this->assertInstanceOf('\Phavour\Auth\Exception\InvalidCredentialsException', $e);
            return;
        }
        $this->fail('expected exception');
    }

    public function testServiceInvalidReturnCode()
    {
        $this->initLoggedOutSession();
        $adapter = new TestCaseAdapter();
        $adapter->setCredentials('jane@example.com', 'not-jane-password');
        $adapter->setReturnFakeCode();
        $service = new Service($adapter);
        try {
            $service->login();
        } catch (Exception $e) {
            $this->assertInstanceOf('\Phavour\Auth\Exception\UnrecognisedAuthenticationResultException', $e);
            return;
        }
        $this->fail('expected exception');
    }

}