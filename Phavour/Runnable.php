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
namespace Phavour;

use Exception;
use Phavour\Application\Environment;
use Phavour\Cache\AdapterAbstract;
use Phavour\Cache\AdapterNull;
use Phavour\Http\Request;
use Phavour\Http\Response;
use Phavour\Runnable\View;

/**
 * Runnable
 */
abstract class Runnable
{
    /**
     * @var Request|null
     */
    protected $request = null;

    /**
     * @var Response|null
     */
    protected $response = null;

    /**
     * @var View|null
     */
    protected $view = null;

    /**
     * @var Environment|null
     */
    protected $environment = null;

    /**
     * @var AdapterAbstract|null
     */
    protected $cache = null;

    /**
     * @var Router|null
     */
    protected $router = null;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * Redirect the request.
     * @param string $url
     * @param integer $status
     * @throws Exception
     */
    final public function redirect($url, $status = 302)
    {
        $this->response->redirect($url, $status);
    }

    /**
     * Construct the Runnable
     * @param Request $request
     * @param Response $response
     * @param View $view
     * @param Environment $environment
     * @param AdapterAbstract $cacheAdapter
     * @param Router|null $router
     * @param array $config
     */
    final public function __construct(Request $request, Response $response, View $view, Environment $environment, AdapterAbstract $cacheAdapter = null, Router $router = null, array $config = array())
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->environment = $environment;
        $this->cache = $cacheAdapter;
        $this->router = $router;
        $this->config = $config;
    }

    /**
     * Check whether the runnable has been constructed
     * @return boolean
     */
    final public function isConstructed()
    {
        return ($this->request instanceof Request);
    }

    /**
     * Get the request object
     * @return Request
     */
    final public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the view object
     * @return View
     */
    final public function getView()
    {
        return $this->view;
    }

    /**
     * Get the response object
     * @return Response
     */
    final public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the cache adapter
     * @return AdapterAbstract
     */
    final public function getCache()
    {
        if (null == $this->cache) {
            $this->cache = new AdapterNull();
        }

        return $this->cache;
    }

    /**
     * Get a config value by specifying the key name
     * @param string|null $key
     * @return mixed|null
     */
    final public function config($key)
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
    final public function getConfig()
    {
        return $this->config;
    }

    /**
     * Stub for users to extend. Called directly after __construct
     * @return bool
     */
    public function init()
    {
        return true;
    }

    /**
     * Called at the end of the process.
     * @return boolean
     * @throws Application\Exception\PackageNotFoundException
     * @throws View\Exception\ViewFileNotFoundException
     */
    final public function finalise()
    {
        if ($this->view->isEnabled()) {
            $this->view->setResponse($this->response);
            $this->view->render();
        }
        return true;
    }

    /**
     * Get a route path by a given name
     * @param string $routeName
     * @param array $params (optional)
     * @return string
     */
    final public function urlFor($routeName, array $params = array())
    {
        if (null === $this->router) {
            return '';
        }

        return $this->router->urlFor($routeName, $params);
    }

    /**
     * When you need to send a not found in your runnable, you
     * can call this directly.
     * Optionally, you can specify the package name, class name,
     * and method name to render accordingly.
     * @param string $package (optional) default 'DefaultPackage'
     * @param string $class (optional) default 'Error'
     * @param string $method (optional) default 'notFound'
     * @return void
     */
    public function notFound($package = 'DefaultPackage', $class = 'Error', $method = 'notFound')
    {
        $this->response->setStatus(404);
        $this->view->setPackage($package);
        $this->view->setClass($class);
        $this->view->setScriptName($method);
        return;
    }
}
