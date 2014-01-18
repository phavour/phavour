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
namespace Phavour\Tests\Autoload;

use \Phavour\Autoload\ApcClassLoader;

class ApcClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testUnknownMethodsProxyToTheRealAutoloader()
    {
        $loader = $this->getMock('Composer\\Autoload\\ClassLoader', array('setUseIncludePath'), array(), '', false);
        $loader->expects($this->once())->method('setUseIncludePath');

        $apcLoader = new ApcClassLoader('phavour_phpunit', $loader);
        $apcLoader->setUseIncludePath(true);
    }

    public function testRegisterAndUnregisterAutoloaderWorks()
    {
        $before = count(spl_autoload_functions());
        $loader = $this->getMock('Composer\\Autoload\\ClassLoader', array('findFile'), array(), '', false);

        $apcLoader = new ApcClassLoader('phavour_phpunit', $loader);
        $apcLoader->register();

        $after = count(spl_autoload_functions());
        $this->assertEquals($after, $before+1);

        $apcLoader->unregister();

        $after = count(spl_autoload_functions());
        $this->assertEquals($after, $before);
    }

    public function testLoadKnownClassReturnsPathToTheClass()
    {
        $file = __DIR__ . '/../testdata/ApcClassLoaderClassExample.php';

        $loader = $this->getMock('Composer\\Autoload\\ClassLoader', array('findFile'), array(), '', false);
        $loader->expects($this->once())->method('findFile')->will($this->returnValue($file));

        $apcLoader = new ApcClassLoader('phavour_phpunit', $loader);

        $return = $apcLoader->loadClass('\\Phavour\\Tests\\testdata\\ApcClassLoaderClassExample');
        $this->assertEquals($return, true);
    }

    public function testLoadUnknownClassReturnsNull()
    {
        $loader = $this->getMock('Composer\\Autoload\\ClassLoader', array('findFile'), array(), '', false);
        $loader->expects($this->once())->method('findFile')->will($this->returnValue(null));

        $apcLoader = new ApcClassLoader('phavour_phpunit', $loader);

        $return = $apcLoader->loadClass('\\Unknown\\Class\\Name');
        $this->assertEquals($return, null);
    }

    public function testSecondCallHitsCache()
    {
        $loader = $this->getMock('Composer\\Autoload\\ClassLoader', array('findFile'), array(), '', false);
        $loader->expects($this->once())->method('findFile')->will($this->returnValue('/path/to/file'));

        $apcLoader = new ApcClassLoader('phavour_phpunit', $loader);

        $apcLoader->findFile('\\path\\to\\file');
        $apcLoader->findFile('\\path\\to\\file');
    }
}
