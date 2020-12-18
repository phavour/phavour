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
use Phavour\Auth;

/**
 * AuthTest
 */
class AuthTest extends SessionTestBase
{
    /**
     * @throws Exception
     */
    public function testIsLoggedOut()
    {
        $this->initLoggedOutSession();
        $this->assertFalse(Auth::getInstance()->isLoggedIn());
    }

    /**
     * @throws Exception
     */
    public function testDoesNotHaveRole()
    {
        $this->initLoggedOutSession();
        $this->assertFalse(Auth::getInstance()->hasRole('admin'));
    }

    /**
     * @throws Exception
     */
    public function testZeroRoles()
    {
        $this->initLoggedOutSession();
        $this->assertFalse(Auth::getInstance()->getRoles());
    }

    /**
     * @throws Exception
     */
    public function testNoIdentity()
    {
        $this->initLoggedOutSession();
        $this->assertFalse(Auth::getInstance()->getIdentity());
    }

    /**
     * @throws Exception
     */
    public function testIsLoggedInRole()
    {
        $this->initLoggedInSession();
        $this->assertTrue(Auth::getInstance()->isLoggedIn());
    }

    /**
     * @throws Exception
     */
    public function testDoesHaveRole()
    {
        $this->initLoggedInSession();
        $this->assertTrue(Auth::getInstance()->hasRole('admin'));
    }

    /**
     * @throws Exception
     */
    public function testTwoRoles()
    {
        $this->initLoggedInSession();
        $this->assertCount(2, Auth::getInstance()->getRoles());
    }

    /**
     * @throws Exception
     */
    public function testTwoIdentityKeys()
    {
        $this->initLoggedInSession();
        $this->assertCount(2, Auth::getInstance()->getIdentity());
    }

    /**
     * @throws Exception
     */
    public function testRemoveRole()
    {
        $this->initLoggedInSession();
        Auth::getInstance()->removeRole('admin');
        $this->assertCount(1, Auth::getInstance()->getRoles());
        $this->assertFalse(Auth::getInstance()->removeRole('nosuchrole'));
        $auth = Auth::getInstance();
        $auth->destroy();
        $this->assertFalse($auth->removeRole('qwerty'));
        $auth->setup();
        $auth->getStorage()->lock();
        $auth->addRole('admin');
        $auth->removeRole('admin');
    }

    /**
     * @throws Exception
     */
    public function testAddRole()
    {
        $this->initLoggedInSession();
        $auth = Auth::getInstance();
        $auth->addRole('master');
        $this->assertCount(3, Auth::getInstance()->getRoles());
        $this->assertTrue($auth->getStorage()->unlock());
        $auth->getStorage()->set('roles', false);
        $auth->getStorage()->lock();
        $this->assertFalse(Auth::getInstance()->getRoles());
        $auth->addRole('one');
        $this->assertCount(1, Auth::getInstance()->getRoles());
        $auth->destroy();
        $this->assertFalse($auth->addRole('nope'));
        $auth->setup();
    }

    /**
     * @throws Exception
     */
    public function testSetRoles()
    {
        $this->initLoggedInSession();
        $auth = Auth::getInstance();
        $auth->setRoles(array('one', 'two', 'three', 'four'));
        $this->assertTrue($auth->getStorage()->lock());
        $auth->setRoles(array('one', 'two', 'three', 'four'));
        $this->assertCount(4, Auth::getInstance()->getRoles());
    }

    /**
     * @throws Exception
     */
    public function testSetExistingRole()
    {
        $this->initLoggedInSession();
        Auth::getInstance()->login(array('abc' => '123'));
        $this->assertCount(0, Auth::getInstance()->getRoles());
        Auth::getInstance()->addRole('admin');
        $this->assertCount(1, Auth::getInstance()->getRoles());
        Auth::getInstance()->addRole('admin');
        $this->assertCount(1, Auth::getInstance()->getRoles());
    }

    /**
     * @throws Exception
     */
    public function testAddToIdentity()
    {
        $this->initLoggedInSession();
        $auth = Auth::getInstance();
        $auth->addToIdentity(array('address' => '123 Neverland Lane'));
        $this->assertCount(3, Auth::getInstance()->getIdentity());
        $this->assertTrue($auth->getStorage()->lock());
        $auth->addToIdentity(array('city' => 'Wonderville'));
        $this->assertCount(4, Auth::getInstance()->getIdentity());
        $auth->getStorage()->unlock();
        $auth->getStorage()->set('identity', false);
        $auth->addToIdentity(array('country' => 'USA'));
        $this->assertCount(1, Auth::getInstance()->getIdentity());
    }

    /**
     * @throws Exception
     */
    public function testSetIdentityEmpty()
    {
        $this->initLoggedInSession();
        Auth::getInstance()->login(array());
        $this->assertFalse(Auth::getInstance()->getIdentity());
    }

    /**
     * @throws Exception
     */
    public function testLoginAndOut()
    {
        $this->initLoggedInSession();
        $this->assertTrue(Auth::getInstance()->isLoggedIn());
        Auth::getInstance()->getStorage()->lock();
        Auth::getInstance()->login(array('123' => '456'));
        Auth::getInstance()->logout();
        $this->initLoggedOutSession();
        $this->assertFalse(Auth::getInstance()->isLoggedIn());
    }

    /**
     * @throws Exception
     */
    public function testLoginNoInstance()
    {
        $this->initLoggedInSession();
        $auth = Auth::getInstance();
        $auth->destroy();
        $this->assertFalse($auth->login(array('123' => 'abc')));
    }

    /**
     * @throws Exception
     */
    public function testAddToNoIdentity()
    {
        $this->initLoggedInSession();
        $auth = Auth::getInstance();
        $auth->destroy();
        $this->assertFalse($auth->addToIdentity(array('123' => 'abc')));
    }

    /**
     * @throws Exception
     */
    public function testGetStorage()
    {
        $this->initLoggedInSession();
        $auth = Auth::getInstance();
        $auth->setup();
        $this->assertInstanceOf('\Phavour\Session\Storage', $auth->getStorage());
    }

    /**
     * @throws Exception
     */
    public function testSetRolesNoStorage()
    {
        $this->initLoggedInSession();
        $auth = Auth::getInstance();
        $auth->setup();
        $auth->destroy();
        $this->assertFalse($auth->setRoles(array('1', '2', '3')));
    }

    /**
     * @throws Exception
     */
    public function testRawLockedUnlocked()
    {
        $this->initLoggedInSession();
        $auth = Auth::getInstance();
        $auth->setup();
        $this->assertTrue($auth->getStorage()->isLocked());
        $this->assertTrue($auth->getStorage()->unlock());
        $this->assertFalse($auth->getStorage()->isLocked());
    }

    /**
     * @throws Exception
     */
    public function testGetNoStorage()
    {
        $this->initLoggedInSession();
        $auth = Auth::getInstance();
        $auth->destroy();
        $this->assertFalse($auth->getStorage());
    }
}
