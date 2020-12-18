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
namespace Phavour\PhavourTests\DefaultPackage\src;

use Phavour\Application\Exception\PackageNotFoundException;
use Phavour\DebuggableException;
use Phavour\Runnable;
use Phavour\Runnable\View\Exception\LayoutFileNotFoundException;

/**
 * Class Index
 * @package Phavour\PhavourTests\DefaultPackage\src
 * @noinspection PhpUnused
 */
class Index extends Runnable
{
    /**
     * @return bool|void
     * @throws DebuggableException
     * @noinspection PhpUnused
     */
    public function init()
    {
        if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
            $e = new DebuggableException('You can only access this method from 127.0.0.1');
            $e->setAdditionalData('Reason', 'Coded check of IP at: ' . __METHOD__);
            throw $e;
        }
    }

    /**
     * @throws LayoutFileNotFoundException
     * @throws PackageNotFoundException
     * @noinspection PhpUnused
     */
    public function index()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->data = 'I\'m from the runnable!';
        $this->view->setLayout('default.phtml');
    }

    /**
     * @noinspection PhpUnused
     */
    public function middleware()
    {
        $this->view->disableView();
    }
}