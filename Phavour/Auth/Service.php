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
namespace Phavour\Auth;

use Exception;
use Phavour\Auth;
use Phavour\Auth\Exception\InvalidCredentialsException;
use Phavour\Auth\Exception\UnrecognisedAuthenticationResultException;

/**
 * \Phavour\Auth\Service
 *
 * This class provides a basic methods of authenticating a user, and ties into \Phavour\Auth
 * @see https://github.com/rogerthomas84/skinny
 */
class Service
{
    /**
     * @var integer
     */
    const PHAVOUR_AUTH_SERVICE_SUCCESS = 1;

    /**
     * @var integer
     */
    const PHAVOUR_AUTH_SERVICE_INVALID = 2;

    /**
     * @var AdapterAbstract|null
     */
    private $adapter;

    /**
     * Set the adapter to authenticate with
     * @param AdapterAbstract $adapter
     */
    public function __construct(AdapterAbstract $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Log a user in using the given adapter from the construct
     * @return bool
     * @throws UnrecognisedAuthenticationResultException
     * @throws InvalidCredentialsException
     * @throws Exception
     */
    public function login()
    {
        $result = $this->adapter->getResult();
        if ($result === self::PHAVOUR_AUTH_SERVICE_SUCCESS) {
            $auth = Auth::getInstance();
            $auth->login($this->adapter->getIdentity());
            $auth->setRoles($this->adapter->getRoles());

            return true;

        } elseif ($result === self::PHAVOUR_AUTH_SERVICE_INVALID) {
            throw new InvalidCredentialsException('Invalid credentials exception');
        }

        throw new UnrecognisedAuthenticationResultException('Expected 1 or 2');
    }
}
