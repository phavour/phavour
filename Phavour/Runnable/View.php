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
namespace Phavour\Runnable;

use Phavour\Http\Response;
use Phavour\Runnable\View\Exception\ViewFileNotFoundException;
use Phavour\Runnable\View\Exception\LayoutFileNotFoundException;
use Phavour\Router;

/**
 * @author Roger Thomas
 * View
 */
class View
{
    /**
     * @var string
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * An array of variables.
     * @var string
     */
    private $runnableVariables = array();

    /**
     * The package name
     * @var string|null
     */
    private $package = null;

    /**
     * The view body
     * @var string
     */
    private $viewBody = null;

    /**
     * The layout location
     * @var string
     */
    private $layoutLocation = null;

    /**
     * The class name
     * @var string|null
     */
    private $class = null;

    /**
     * Instance of Router
     * @var Router|null
     */
    private $router = null;

    /**
     * The response object
     * @var Response
     */
    private $response = null;

    /**
     * The method name
     * @var string|null
     */
    private $method = null;

    /**
     * The application path.
     * @var string|null
     */
    private $appPath = null;

    /**
     * Whether a view is enabled
     * @var boolean
     */
    private $viewIsEnabled = true;

    /**
     * @var string|null
     */
    private $currentView = null;

    /**
     * Construct giving the details of the route
     * @param string $package
     * @param string $class
     * @param string $method
     */
    public function __construct($package, $class, $method)
    {
        $this->package = $this->treatPackageName($package);
        $this->class = $class;
        $this->method = $method;
    }

    /**
     * Normalise the package name.
     * @param string $package
     * @return string
     */
    private function treatPackageName($package)
    {
        if (mb_strtolower(mb_substr($package, -7)) != 'package') {
            $package = $package . 'Package';
        }

        return $package;
    }

    /**
     * Get the package name
     * @return string
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Get the class name
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get the method name
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get the real path to the application
     * @return string
     */
    public function getApplicationPath()
    {
        return $this->appPath;
    }

    /**
     * Set the package name
     * @param string $package
     */
    public function setPackage($package)
    {
        if ($package != $this->package) {
            $this->layoutLocation = null;
        }
        $this->package = $this->treatPackageName($package);
    }

    /**
     * Set the class name
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Set the real path to the application root folder.
     * @param string $path
     */
    public function setApplicationPath($path)
    {
        $this->appPath = $path;
    }

    /**
     * Set the script name (usually the method name)
     * @param string $method
     */
    public function setScriptName($method)
    {
        $this->method = $method;
    }

    /**
     * Set the router
     * @param Router|null $router
     */
    public function setRouter(Router $router = null)
    {
        $this->router = $router;
    }

    /**
     * Get a route path by a given name
     * @param string $routeName
     * @param array $params (optional)
     * @return string
     */
    public function urlFor($routeName, array $params = array())
    {
        if (null === $this->router) {
            return '';
        }

        return $this->router->urlFor($routeName, $params);
    }

    /**
     * Magic method to call $view->myvar = 'x';
     * @param string|integer $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->runnableVariables[$name] = $value;
    }

    /**
     * Magic method to call $x = $view->myvar (returns null if not set)
     * @param string $name
     * @return string|null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->runnableVariables)) {
            return $this->runnableVariables[$name];
        }
        return null;
    }

    /**
     * Set a variable for the view
     * @param string|integer $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $this->runnableVariables[$name] = $value;
    }

    /**
     * Get a value from the view. (returns null if not set)
     * @param string $name
     * @return string|null
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->runnableVariables)) {
            return $this->runnableVariables[$name];
        }
        return null;
    }

    /**
     * Set the view as enabled
     */
    public function enableView()
    {
        $this->viewIsEnabled = true;
    }

    /**
     * Set the view as disabled
     */
    public function disableView()
    {
        $this->viewIsEnabled = false;
    }

    /**
     * Set the response object
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Check if the view is enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->viewIsEnabled;
    }

    /**
     * Set the layout location
     * @param string $file
     */
    public function setLayout($file)
    {
        if (strstr($file, '::')) {
            $file = explode('::', $file);
            $package = $file[0];
            $file = $file[1];
        } else {
            $package = $this->package;
        }

        $file = $this->assignDeclaredLayout($package, $file);

    }

    /**
     * Render the view
     * @param string $methodName
     * @param string $class
     * @param string $package
     * @return boolean if no view, otherwise sends the response
     */
    public function render($methodName = null, $class = null, $package = null)
    {
        if (!$this->isEnabled()) {
            return true;
        }

        if (null != $package) {
            $this->package = $this->treatPackageName($package);
        }

        if (null != $class) {
            $this->class = $class;
        }

        if (null != $methodName) {
            $this->method = $methodName;
        }

        $this->currentView = $this->getPathForView();
        @ob_start();
        @include $this->currentView;
        $this->viewBody = @ob_get_clean();
        if (!empty($this->layoutLocation)) {
            @ob_start();
            @include $this->layoutLocation;
            $this->viewBody = @ob_get_clean();
        }
        $this->response->setBody($this->viewBody);
        $this->response->sendResponse();
    }

    /**
     * Retrieve the view content
     * @return string
     */
    private function content()
    {
        return $this->viewBody;
    }

    /**
     * Assign the path to the layout file
     * @param string $package
     * @param string $file
     * @throws \Exception
     * @return boolean
     */
    private function assignDeclaredLayout($package, $file)
    {
        $file = lcfirst($file);
        if (substr(strtolower($file), -6) == '.phtml') {
            $file = substr($file, 0, -6);
        }
        $pathPieces = array($this->appPath, 'src', $package, 'res', 'layout', $file);
        $path = implode(self::DS, $pathPieces) . '.phtml';
        if (file_exists($path)) {
            $this->layoutLocation = $path;
            return true;
        }
        $e = new LayoutFileNotFoundException('Invalid layout file path, expected: "' . $path . '"');
        $e->setAdditionalData('Layout Path Checked: ', $path);
        throw $e;
    }

    /**
     * Get the path for a view file.
     * @throws \Exception
     * @return string
     */
    private function getPathForView()
    {
        $methodName = lcfirst($this->method);
        $className = lcfirst($this->class);
        $pathPieces = array($this->appPath, 'src', $this->package, 'res', 'view', $className, $methodName);
        $path = implode(self::DS, $pathPieces) . '.phtml';
        if (file_exists($path)) {
            return $path;
        }
        $e = new ViewFileNotFoundException('Invalid view file path, expected: "' . $path . '"');
        $e->setAdditionalData('View Path Checked: ', $path);
        throw $e;
    }

    /**
     * Inflate a given
     * @param string $filePath
     * @throws \Phavour\Runnable\View\Exception\ViewFileNotFoundException
     */
    private function inflate($filePath)
    {
        $currentDirectory = rtrim(dirname($this->currentView), self::DS);
        $currentDirectory .= self::DS;
        if (substr($filePath, -6, 6) != '.phtml') {
            $filePath .= '.phtml';
        }

        $searchPath = realpath($currentDirectory . $filePath);
        if ($searchPath != false && file_exists($searchPath)) {
            $content = @include $searchPath;
            if (!$content) {
                // @codeCoverageIgnoreStart
                $e = new ViewFileNotFoundException('Unable to inflate file "' . $searchPath . '"');
                $e->setAdditionalData('View Path: ', $searchPath);
                throw $e;
                // @codeCoverageIgnoreEnd
            }
            return;
        }

        // @codeCoverageIgnoreStart
        $e = new ViewFileNotFoundException('Invalid view file path, expected: "' . $currentDirectory . $filePath . '"');
        $e->setAdditionalData('View Path Checked: ', $currentDirectory . $filePath);
        throw $e;
        // @codeCoverageIgnoreEnd
    }
}
