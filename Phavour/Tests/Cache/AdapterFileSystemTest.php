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
namespace Phavour\Tests\Cache;

use Phavour\Cache\AdapterFileSystem;
use Phavour\Helper\FileSystem\Directory;
use PHPUnit\Framework\TestCase;

/**
 * AdapterFileSystemTest
 */
class AdapterFileSystemTest extends TestCase
{
    /**
     * @var AdapterFileSystem
     */
    private $adapter = null;

    /**
     * @var Directory
     */
    private $dir = null;

    /**
     * @var string|null
     */
    private $path = null;

    /**
     * @var string|null
     */
    private $name = 'abcd';

    public function setUp(): void
    {
        $this->dir = new Directory();
        $tmpDir = sys_get_temp_dir();
        $this->dir->createPath($tmpDir, 'PhavourTests');
        $this->path = $tmpDir . Directory::DS . 'PhavourTests';
        $this->adapter = new AdapterFileSystem(array('path' => $this->path));
    }

    public function testSetGet()
    {
        $this->adapter->set($this->name, 'foobar', 10);
        $this->assertEquals('foobar', $this->adapter->get($this->name));
    }

    public function testAssertException()
    {
        try {
        	$a = new AdapterFileSystem(array());
        } catch (\Exception $e) {
            $this->assertStringContainsString('key must be specified', $e->getMessage());
            return;
        }
        $this->fail('expected exception');
    }

    public function testGetInvalid()
    {
        $name = rand(0,999);
        $md5 = md5($name);
        $file = $this->path . Directory::DS . substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2) . Directory::DS . 'PCAFS_' . $md5;
        $this->dir->createPath($this->path, substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2));
        $handle = fopen($file, 'w');
        fwrite($handle, (time()+100) . PHP_EOL . 'a;x/sdr');
        fclose($handle);
        chmod($file, 701);
        $adapter = clone $this->adapter;
        $this->assertFalse($adapter->get($name));

        $name = rand(0,999);
        $md5 = md5($name);
        $file = $this->path . Directory::DS . substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2) . Directory::DS . 'PCAFS_' . $md5;
        $this->dir->createPath($this->path, substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2));
        $handle = fopen($file, 'w');
        fwrite($handle, (time()+100) . PHP_EOL . 'a;x/sdr');
        fclose($handle);
        $adapter = clone $this->adapter;
        $this->assertFalse($adapter->get($name));

        $name = rand(0,999);
        $md5 = md5($name);
        $file = $this->path . Directory::DS . substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2) . Directory::DS . 'PCAFS_' . $md5;
        $this->dir->createPath($this->path, substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2));
        $handle = fopen($file, 'w');
        fwrite($handle, 'abc');
        fclose($handle);
        $adapter = clone $this->adapter;
        $this->assertFalse($adapter->get($name));

        $name = rand(0,999);
        $md5 = md5($name);
        $file = $this->path . Directory::DS . substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2) . Directory::DS . 'PCAFS_' . $md5;
        $this->dir->createPath($this->path, substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2));
        $handle = fopen($file, 'w');
        fwrite($handle, 'abc');
        fclose($handle);
        $adapter = clone $this->adapter;
        $this->assertFalse($adapter->renew($name, 100));

        $name = rand(0,999);
        $md5 = md5($name);
        $file = $this->path . Directory::DS . substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2) . Directory::DS . 'PCAFS_' . $md5;
        $this->dir->createPath($this->path, substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2));
        $handle = fopen($file, 'w');
        fwrite($handle, (time()+100) . PHP_EOL . 'abc');
        fclose($handle);
        $adapter = clone $this->adapter;
        $this->assertFalse($adapter->renew($name, 100));

        $name = rand(0,999);
        $md5 = md5($name);
        $file = $this->path . Directory::DS . substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2) . Directory::DS . 'PCAFS_' . $md5;
        $this->dir->createPath($this->path, substr($md5, 0, 2) . Directory::DS . substr($md5, 2, 2));
        $handle = fopen($file, 'w');
        fwrite($handle, 0);
        fclose($handle);
        $adapter = clone $this->adapter;
        $this->assertFalse($adapter->get($name));
    }

    public function testCannotCreatePath()
    {
        $path = $this->path . Directory::DS . 'nowrite';
        @mkdir($path);
        chmod($path, 666);
        $adapter = new AdapterFileSystem(array('path' => $path));
        $this->assertFalse($adapter->set('abc', 'def', 100));
        rmdir($path);
        $adapter->renew('abc', 'def', 100);
    }

    public function testSetRenew()
    {
        $this->assertTrue($this->adapter->set($this->name, 'abcfoobar', 10));
        $this->assertTrue($this->adapter->renew($this->name, 10));
        $this->assertFalse($this->adapter->renew(md5(microtime()), 100));
    }

    public function testSetRemove()
    {
        $this->assertTrue($this->adapter->set('foo', 'abcfoobar', 10));
        $this->assertTrue($this->adapter->remove('foo'));
        $this->assertFalse($this->adapter->remove(md5(time() + rand(0,99))));
    }

    public function testHas()
    {
        $this->adapter->set($this->name, 'abcfoobar', 10);
        $this->assertTrue($this->adapter->has($this->name));
        $this->assertEquals('abcfoobar', $this->adapter->get($this->name));
        $this->adapter->remove($this->name);
        $this->assertFalse($this->adapter->has($this->name));
    }

    public function testRemove()
    {
        $this->adapter->set($this->name, 'abcfoobar', 10);
        $this->adapter->remove($this->name);
        $this->assertFalse($this->adapter->has($this->name));
    }

    public function testFlush()
    {
        $this->assertTrue($this->adapter->flush());
    }
}
