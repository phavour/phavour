<?php
/**
 * Phavour PHP Framework Library
 *
 * @author      Roger Thomas <roger.thomas@rogerethomas.com>
 * @copyright   2013 Roger Thomas
 * @link        http://www.rogerethomas.com
 * @license     http://www.rogerethomas.com/license
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
namespace Phavour\Tests\Helper\FileSystem;

use Phavour\Helper\FileSystem\Directory;

/**
 * @author Roger Thomas
 * DirectoryTest
 */
class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeInvalids()
    {
        $fullPath = sys_get_temp_dir() . Directory::DS . 'PhavourTests';
        @mkdir($fullPath);
        $helper = new Directory();
        $makeFake = $helper->createPath(Directory::DS . 'no' . Directory::DS . 'such' . Directory::DS . 'path', 'ok');
        $this->assertFalse($makeFake);
        $makeEmptyPath = $helper->createPath($fullPath, '');
        $this->assertFalse($makeEmptyPath);
        // cover continue for empty paths.
        $directoryCreate = $helper->createPath($fullPath, 'tests' . Directory::DS . 'directory');
        $this->assertTrue($directoryCreate);

        $helper->recursivelyDeleteFromDirectory($fullPath);
    }

    public function testRecusiveDelete()
    {
        $fullPath = sys_get_temp_dir() . Directory::DS . 'PhavourTests';
        mkdir($fullPath);
        touch($fullPath . Directory::DS . '1');
        $this->assertFileExists($fullPath . Directory::DS . '1');
        @mkdir($fullPath . Directory::DS . 'dir');
        touch($fullPath . Directory::DS . 'dir' . Directory::DS . '2');
        $this->assertFileExists($fullPath . Directory::DS . 'dir' . Directory::DS . '2');
        @mkdir($fullPath . Directory::DS . 'dir');
        $dir = new Directory();
        $dir->recursivelyDeleteFromDirectory($fullPath . Directory::DS . '1');
        $dir->recursivelyDeleteFromDirectory($fullPath);
        $this->assertFileNotExists($fullPath . Directory::DS . '1');
        $this->assertFileNotExists($fullPath . Directory::DS . 'dir' . Directory::DS . '2');
    }
}
