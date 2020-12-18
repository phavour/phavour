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
namespace Phavour\Helper\FileSystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Directory
 */
class Directory
{
    /**
     * @var string
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Create a new path on top of a base directory
     * @param string $base
     * @param string $path
     * @return boolean
     */
    public function createPath($base, $path)
    {
        if (!is_dir($base) || !is_writable($base)) {
            return false;
        }

        if (empty($path)) {
            return false;
        }

        $pieces = explode(self::DS, $path);
        $dir = $base;
        foreach ($pieces as $directory) {
            if (empty($directory)) {
                // @codeCoverageIgnoreStart
                continue; // Handle the / from the exploded path
                // @codeCoverageIgnoreEnd
            }

            $singlePath = $dir . self::DS . $directory;
            if (!file_exists($singlePath) || !is_dir($singlePath)) {
                $create = @mkdir($singlePath);
                if ($create === false) {
                    // @codeCoverageIgnoreStart
                    return false;
                    // @codeCoverageIgnoreEnd
                }
            }
            $dir = $singlePath;
        }

        return true;
    }

    /**
     * Recursively delete files and folders, when given a base path
     * @param string $baseDirectory
     * @return void
     */
    public function recursivelyDeleteFromDirectory($baseDirectory)
    {
        if (!is_dir($baseDirectory)) {
            return;
        }

        $iterator = new RecursiveDirectoryIterator($baseDirectory);
        $files = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            if ($fileName == '.' || $fileName == '..') {
                continue;
            }

            if ($file->isDir()) {
                $this->recursivelyDeleteFromDirectory($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($baseDirectory);

        return;
    }
}
