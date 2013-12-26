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
namespace Phavour;

use Phavour\Application\Environment;
use Phavour\Application\Exception\PackagesNotFoundException;
use Phavour\Application\Exception\RunnableNotFoundException;
use Phavour\Config\FromArray;
use Phavour\Debug\FormattedException;
use Phavour\Http\Request;
use Phavour\Http\Response;
use Phavour\Router\Exception\RouteMissingPackageNameException;
use Phavour\Router\Exception\RouteNotFoundException;
use Phavour\Runnable;
use Phavour\Runnable\View;
use Phavour\Cache\AdapterAbstract;
use Phavour\Cache\AdapterNull;
use Phavour\Application\Exception\ApplicationIsAlreadySetupException;

/**
 * @author Roger Thomas
 * Application
 */
class Application
{
    /**
     * @var string
     */
    protected $appDirectory = null;

    /**
     * @var Environment|null
     */
    protected $env = null;

    /**
     * PackageName => PackageLocation
     * @var array
     */
    protected $packages = array();

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var array
     */
    protected $routes = array();

    /**
     * @var AdapterAbstract|null
     */
    protected $cache = null;

    /**
     * @var Router|null
     */
    protected $router = null;

    /**
     * @var boolean
     */
    protected $isSetup = false;

    /**
     * @var string|null
     */
    private $mode = null;

    /**
     * @var Response|null
     */
    private $response = null;

    /**
     * @var Request|null
     */
    private $request = null;

    /**
     * Construct, giving the realpath to the application root folder.
     * @param string $appDirectory
     */
    public function __construct($appDirectory)
    {
        $this->appDirectory = $appDirectory;
    }

    /**
     * Set an application level cache adapter.
     * This adapter will be made available to all runnables
     *
     * @param AdapterAbstract $adapter
     */
    public function setCacheAdapter(AdapterAbstract $adapter)
    {
        if ($this->isSetup) {
            $e = new ApplicationIsAlreadySetupException('You cannot set the cache after calling setup()');
            $this->error($e);
        }

        $this->cache = $adapter;
    }

    /**
     * Setup the application, assigns all the packages, config,
     * routes
     */
    public function setup()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->env = new Environment();
        $this->mode = $this->env->getMode();
        if (!$this->env->isProduction() || $this->cache == null) {
            $this->cache = new AdapterNull();
        }
        $this->loadPackages();
        $this->loadConfig();
        if (array_key_exists('ini.set', $this->config)) {
            foreach ($this->config['ini.set'] as $iniName => $iniValue) {
                // @codeCoverageIgnoreStart
                ini_set($iniName, $iniValue);
                // @codeCoverageIgnoreEnd
            }
        }
        $this->loadRoutes();
        $this->isSetup = true;
    }

    /**
     * Run the application.
     */
    public function run()
    {
        if (empty($this->packages)) {
            $this->setup();
            if (empty($this->packages)) {
                // @codeCoverageIgnoreStart
                $e = new PackagesNotFoundException('Application has no packages.');
                $e->setAdditionalData('Application Path', $this->appDirectory);
                $this->error($e);
                return;
                // @codeCoverageIgnoreEnd
            }
        }

        $this->router = new Router();
        $this->router->setRoutes($this->routes);
        $this->router->setMethod($this->request->getRequestMethod());
        $this->router->setPath($this->request->getRequestUri());
        try {
            $route = $this->router->getRoute();
        } catch (RouteNotFoundException $e) {
            $this->notFound($e);
            return;
        }
        $package = $route['package'];
        $runnable = $route['runnable'];
        $runnables = explode('::', $runnable);
        $classString = '\\' . $package . '\\src\\' . $runnables[0];
        if (class_exists($classString)) {
            try {
                $instance = $this->getRunnable($package, $runnables[0], $runnables[1], $classString);
                if (is_callable(array($instance, $runnables[1]))) {
                    call_user_func_array(array($instance, $runnables[1]), $route['params']);
                    $instance->finalise();
                    return;
                }
            // @codeCoverageIgnoreStart
            } catch (\Exception $e) {
                $this->error($e);
                return;
            }

            $this->notFound(new RunnableNotFoundException('No such runnable: ' . $classString . '::' . $runnables[1]));
        }

        $this->notFound(new RunnableNotFoundException('No such runnable: ' . $classString . '::' . $runnables[1]));
    }
    // @codeCoverageIgnoreEnd

    /**
     * Load and assign the packages array to the
     * name => filePath format.
     */
    private function loadPackages()
    {
        $cacheName = $this->mode . '_Phavour_Application_packages';
        if (false != ($packages = $this->cache->get($cacheName))) {
            // @codeCoverageIgnoreStart
            $this->packages = $packages;
            return;
            // @codeCoverageIgnoreEnd
        }

        $found = scandir($this->appDirectory . DIRECTORY_SEPARATOR . 'src');
        foreach ($found as $packageName) {
            $packagePath = $this->appDirectory . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $packageName;
            if (substr($packageName, -7) == 'Package' && is_dir($packagePath)) {
                $this->packages[$packageName] = $packagePath;
            }
        }

        $this->cache->set($cacheName, $this->packages, 86400);
    }

    /**
     * Load and assign the config files from the packages and
     * the main /res/config.php file
     */
    private function loadConfig()
    {
        $cacheName = $this->mode . '_Phavour_Application_config';
        if (false != ($configs = $this->cache->get($cacheName))) {
            // @codeCoverageIgnoreStart
            $this->config = $configs;
            return;
            // @codeCoverageIgnoreEnd
        }

        $config = array();
        foreach ($this->packages as $packageName => $packageLocation) {
            try {
                $finder = new FromArray(
                    $packageLocation . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'config.php'
                );
                $proposedConfig = $finder->getArrayWhereKeysBeginWith($packageName);
                if (is_array($proposedConfig)) {
                    $config = array_merge($config, $proposedConfig);
                }
            } catch (\Exception $e) {
                // Package doesn't have config
            }
        }

        try {
            $finder = new FromArray(
                $this->appDirectory . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'config.php'
            );
            $appConfig = $finder->getArray();
            if (is_array($appConfig)) {
                $config = array_merge($config, $appConfig);
            }
        } catch (\Exception $e) {
            // No config overrides set
        }

        $this->config = $config;
        $this->cache->set($cacheName, $this->config, 86400);
    }

    /**
     * Load and assign the route files from the packages and
     * the main /res/routes.php file
     */
    private function loadRoutes()
    {
        $cacheName = $this->mode . '_Phavour_Application_routes';
        if (false != ($routes = $this->cache->get($cacheName))) {
            // @codeCoverageIgnoreStart
            $this->routes = $routes;
            return;
            // @codeCoverageIgnoreEnd
        }

        $routes = array();
        foreach ($this->packages as $packageName => $packageLocation) {
            try {
                $finder = new FromArray(
                    $packageLocation . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'routes.php'
                );
                $proposedRoutes = $finder->getArray();
                if (is_array($proposedRoutes)) {
                    foreach ($proposedRoutes as $key => $routeDetails) {
                        if (!array_key_exists('package', $routeDetails)) {
                            $proposedRoutes[$key]['package'] = $packageName;
                        }
                        $proposedRoutes[$key]['package'] = $packageName;
                    }
                    $routes = array_merge($routes, $proposedRoutes);
                }
            } catch (\Exception $e) {
                // Package doesn't have routes
            }
        }

        try {
            $finder = new FromArray(
                $this->appDirectory . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'routes.php'
            );
            $appRoutes = $finder->getArray();
            if (is_array($appRoutes)) {
                foreach ($appRoutes as $key => $routeDetails) {
                    if (!array_key_exists('package', $routeDetails)) {
                        // @codeCoverageIgnoreStart
                        throw new RouteMissingPackageNameException();
                        // @codeCoverageIgnoreEnd
                    }
                }
                $routes = array_merge($routes, $appRoutes);
            }
        } catch (\Exception $e) {
            // No route overrides set
        }

        $this->routes = $routes;
        $this->cache->set($cacheName, $this->routes, 86400);
    }

    /**
     * In the event of an invalid route, this method will be called
     * by default it'll look for 'DefaultPackage::Error::notFound' before
     * manipulating the response directly and returning to the user.
     * It's environmental sensitive, so will format things accordingly.
     * @param \Exception $throwable
     */
    private function notFound(\Exception $throwable)
    {
        if ($this->env->isProduction()) {
            if (false != ($errorClass = $this->getErrorClass())) {
                try {
                    // TODO : This needs to be moved into a $this->boot() method
                    $this->response->setStatus(404);
                    $instance = $this->getRunnable('DefaultPackage', 'Error', 'notFound', $errorClass);
                    if (is_callable(array($instance, 'notFound'))) {
                        $instance->notFound();
                    } else {
                        // @codeCoverageIgnoreStart
                        $e = new RunnableNotFoundException('Runnable not found');
                        $e->setAdditionalData('Expected: ', '\\DefaultPackage\\src\\Error::notFound()');
                        throw $e;
                        // @codeCoverageIgnoreEnd
                    }
                    $instance->finalise();
                    return;
                // @codeCoverageIgnoreStart
                } catch (\Exception $e) {
                    $this->error($e);
                    return;
                    // @codeCoverageIgnoreEnd
                }
            }

            // @codeCoverageIgnoreStart
            @ob_get_clean();
            $this->response->setStatus(404);
            $this->response->setBody('<h1 style="font:28px/1.5 Helvetica,Arial,Verdana,sans-serif;">404 Not Found</h1>');
            $this->response->sendResponse();
            return;
            // @codeCoverageIgnoreEnd
        } else {
            // @codeCoverageIgnoreStart
            FormattedException::display($throwable);
            return;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * In the event of an uncaught Exception at point of run, this method will be called
     * by default it'll look for 'DefaultPackage::Error::uncaughtError' before
     * manipulating the response directly and returning to the user.
     * It's environmental sensitive, so will format things accordingly.
     * @param \Exception $throwable
     * @codeCoverageIgnore
     */
    private function error(\Exception $throwable)
    {
        if ($this->env->isProduction()) {
            if (false != ($errorClass = $this->getErrorClass())) {
                try {
                    $this->response->setStatus(500);
                    $instance = $this->getRunnable('DefaultPackage', 'Error', 'uncaughtException', $errorClass);
                    if (is_callable(array($instance, 'uncaughtException'))) {
                        $instance->uncaughtException();
                    } else {
                        $e = new RunnableNotFoundException('Runnable not found');
                        $e->setAdditionalData('Expected: ', '\\DefaultPackage\\src\\Error::uncaughtException()');
                        throw $e;
                    }
                    $instance->finalise();
                    return;
                } catch (\Exception $e) {
                    // Ignore, we're already here.
                }
            }

            @ob_get_clean();
            $this->response->setStatus(500);
            $this->response->setBody('<h1 style="font:28px/1.5 Helvetica,Arial,Verdana,sans-serif;">Application Error</h1>');
            $this->response->sendResponse();
            return;
        } else {
            FormattedException::display($throwable);
            return;
        }
    }

    /**
     * Get the class name of the \DefaultPackage\src\Error (if it exists)
     * @return string|boolean false for failure
     */
    private function getErrorClass()
    {
        if (!array_key_exists('DefaultPackage', $this->packages)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $pathToDefaultPackage = $this->packages['DefaultPackage'];
        $ds = DIRECTORY_SEPARATOR;
        $errorClassPath = $pathToDefaultPackage . $ds . 'src' . $ds . 'Error.php';
        if (!file_exists($errorClassPath)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $className = '\\DefaultPackage\\src\\Error';

        if (!class_exists($className)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return $className;
    }

    /**
     * Get the corresponding runnable for the given parameters
     * @param string $package
     * @param string $class
     * @param string $method
     * @param string $className
     * @return \Phavour\Runnable
     */
    private function getRunnable($package, $class, $method, $className)
    {
        $view = $this->getViewFor($package, $class, $method);
        $instance = new $className($this->request, $this->response, $view, $this->env, $this->cache, $this->router);
        /* @var $instance Runnable */
        $instance->init();

        return $instance;
    }

    /**
     * Retrieve an instance of View for a given package, class, method combination
     * @param string $package
     * @param string $class
     * @param string $method
     * @return \Phavour\Runnable\View
     */
    private function getViewFor($package, $class, $method)
    {
        $view = new View($package, $class, $method);
        $view->setApplicationPath($this->appDirectory);
        $view->setRouter($this->router);

        return $view;
    }
}
