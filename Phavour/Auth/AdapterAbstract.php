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

/**
 * \Phavour\Auth\AdapterAbstract
 *
 * This class provides a simple way of constructing adapters to authenticate a user
 * @see https://github.com/rogerthomas84/skinny
 */
abstract class AdapterAbstract
{
    /**
     * Get the final result
     * @see Service::PHAVOUR_AUTH_SERVICE_SUCCESS
     * @see Service::PHAVOUR_AUTH_SERVICE_INVALID
     * @return integer
     */
    public abstract function getResult();

    /**
     * Called on return of PHAVOUR_AUTH_SERVICE_SUCCESS to retrieve
     * the array of user identity
     * @return array
     */
    public abstract function getIdentity();

    /**
     * Called on return of PHAVOUR_AUTH_SERVICE_SUCCESS to retrieve
     * the array of user roles
     * @return array
     */
    public abstract function getRoles();
}
