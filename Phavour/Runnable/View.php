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
namespace Phavour\Runnable;

use Exception;
use Phavour\Application;
use Phavour\Application\Exception\PackageNotFoundException;
use Phavour\Http\Response;
use Phavour\Router;
use Phavour\Runnable\View\Exception\LayoutFileNotFoundException;
use Phavour\Runnable\View\Exception\ViewFileNotFoundException;

/**
 * View
 */
class View
{
    /**
     * @var string
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * An array of variables.
     * @var array
     */
    private $runnableVariables = array();

    /**
     * The config array
     * @var array
     */
    private $config = array();

    /**
     * The package name
     * @var string|null
     */
    private $package;

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
    private $class;

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
    private $method;

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
     * @param Application $app
     * @param string $package
     * @param string $class
     * @param string $method
     */
    public function __construct(Application $app, $package, $class, $method)
    {
        $this->app = $app;
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
     * Get the method name (alias for getScriptName)
     * @return string
     */
    public function getMethod()
    {
        return $this->getScriptName();
    }

    /**
     * Get the script name
     * @return string
     */
    public function getScriptName()
    {
        return $this->method;
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
     * Set the config array, called by Application
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Set the script name (usually the method name)
     * @param string $name
     */
    public function setScriptName($name)
    {
        $this->method = $name;
    }

    /**
     * Set the method name (alias for setScriptName)
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->setScriptName($method);
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
     * Magic method to call $view->myVar = 'x';
     * @param string|integer $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->runnableVariables[$name] = $value;
    }

    /**
     * Magic method to call $x = $view->myVar (returns null if not set)
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
     * Get a config value by specifying the key name
     * @param string|null $key
     * @return mixed|null
     */
    public function config($key)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        return null;
    }

    /**
     * Get the entire configuration array
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
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
     * @throws LayoutFileNotFoundException
     * @throws PackageNotFoundException
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

        $this->assignDeclaredLayout($package, $file);
    }

    /**
     * Render the view
     * @param string $methodName
     * @param string $class
     * @param string $package
     * @return bool|void if no view, otherwise sends the response
     * @throws PackageNotFoundException
     * @throws ViewFileNotFoundException
     * @throws Exception
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
        /** @noinspection PhpIncludeInspection */
        @include $this->currentView;
        $this->viewBody = @ob_get_clean();
        if (!empty($this->layoutLocation)) {
            @ob_start();
            /** @noinspection PhpIncludeInspection */
            @include $this->layoutLocation;
            $this->viewBody = @ob_get_clean();
        }
        $this->response->setBody($this->viewBody);
        $this->response->sendResponse();
    }

    /**
     * Retrieve the view content
     * @return string
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function content()
    {
        return $this->viewBody;
    }

    /**
     * Assign the path to the layout file
     * @param string $package
     * @param string $file
     * @throws LayoutFileNotFoundException
     * @throws PackageNotFoundException
     * @return boolean
     */
    private function assignDeclaredLayout($package, $file)
    {
        $file = lcfirst($file);
        if (substr(strtolower($file), -6) == '.phtml') {
            $file = substr($file, 0, -6);
        }

        $package = $this->app->getPackage($package);
        $pathPieces = array($package['package_path'], 'res', 'layout', $file);
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
     * @throws PackageNotFoundException
     * @throws ViewFileNotFoundException
     * @return string
     */
    private function getPathForView()
    {
        $methodName = lcfirst($this->method);
        $className = lcfirst($this->class);

        $package = $this->app->getPackage($this->package);
        $pathPieces = array($package['package_path'], 'res', 'view', $className, $methodName);
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
     * @throws ViewFileNotFoundException
     * @noinspection PhpUnusedPrivateMethodInspection
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
            /** @noinspection PhpIncludeInspection */
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
