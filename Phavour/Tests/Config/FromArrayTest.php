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
namespace Phavour\Tests\Config;

use Phavour\Config\FromArray;

/**
 * @author Roger Thomas
 * FromArrayTest
 */
class FromArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $filepath = null;

    /**
     * @var string
     */
    private $invalidConfigPath = null;

    public function setUp()
    {
        $this->filepath = realpath(dirname(__FILE__) . '/../testdata/array.php');
        $this->invalidConfigPath = realpath(dirname(__FILE__) . '/../testdata/emptyfile.php');
    }

    public function testTestAllConfigIsReturned()
    {
        $config = new FromArray($this->filepath);
        $this->assertEquals(
            3,
            count($config->getArray())
        );
    }

    public function testTestKeysBeginningWithStringAreReturned()
    {
        $config = new FromArray($this->filepath);
        $this->assertEquals(
            2,
            count($config->getArrayWhereKeysBeginWith('person.'))
        );
    }

    public function testTestInvalidConfigThrowsException()
    {
        try {
            $config = new FromArray($this->invalidConfigPath);
            $this->assertEquals(
                2,
                count($config->getArrayWhereKeysBeginWith('person.'))
            );
        } catch (\Exception $e) {
            $this->assertContains('Config not found', $e->getMessage());
            return;
        }

        $this->fail('Expected invalid config.');
    }
}
