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

use Phavour\Router\Exception\RouteNotFoundException;

/**
 * Router
 */
class Router
{
    /**
     * @var string
     */
    protected $path = '/';

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var array
     */
    private $routes = array();

    /**
     * @var array
     */
    private $resolvedUrls = array();

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get the path provided
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the Request Method
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Get the Request Method
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get the routes provided
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set the routes
     * @param array $routes
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Retrieve the matching route from the parameters given.
     * @throws Exception
     * @return array (or throws Exception)
     */
    public function getRoute()
    {
        foreach ($this->routes as $route) {

            // Is the route array valid
            if (false == ($cleansed = $this->isValidRoute($route))) {
                continue;
            }

            $route = $cleansed;

            // Does request method match
            if (!$this->methodMatches($route)) {
                continue;
            }

            if (false != ($match = $this->isDirectMatch($route))) {
                return $match;
            }

            if (false != ($match = $this->isParameterisedMatch($route))) {
                return $match;
            }
        }

        $e = new RouteNotFoundException('Route for path not found.');
        $e->setAdditionalData('Path: ', $this->path);
        throw $e;
    }

    /**
     * Get a URL for a given route name
     * @param string $routeName
     * @param array $params (optional)
     * @return string
     */
    public function urlFor($routeName, array $params = array())
    {
        $storageKey = md5($routeName . serialize($params));
        if (array_key_exists($storageKey, $this->resolvedUrls)) {
            return $this->resolvedUrls[$storageKey];
        }

        if (!array_key_exists($routeName, $this->routes)) {
            $this->resolvedUrls[$storageKey] = $routeName;
            return $routeName;
        }

        $route = $this->isValidRoute($this->routes[$routeName]);
        if (!$route) {
            $this->resolvedUrls[$storageKey] = $routeName;
            return $routeName;
        }

        $path = $route['path'];
        if (empty($params)) {
            $this->resolvedUrls[$storageKey] = $path;
            return $path;
        }

        foreach ($params as $key => $value) {
            if (is_string($value) || is_int($value)) {
                $path = str_replace('{' . $key . '}', $value, $path);
            }
        }

        $this->resolvedUrls[$storageKey] = $path;

        return $path;
    }

    /**
     * Check if a route matches the path directly
     * @param array $route
     * @return array|boolean false
     */
    private function isDirectMatch(array $route)
    {
        if ($route['path'] == $this->path) {
            return $route;
        }

        return false;
    }

    /**
     * Validate if a route matches based on parameters
     * @param array $route
     * @return array|boolean false
     */
    private function isParameterisedMatch(array $route)
    {
        // Find out if the route has parameters
        if (!preg_match('/\{[a-z]+\}/i', $route['path'])) {
            return false;
        }

        $routePieces = explode('/', $route['path']);
        $urlPieces = explode('/', $this->path);
        if (count($routePieces) != count($urlPieces)) {
            return false;
        }

        $params = array();
        foreach ($routePieces as $routeKey => $routeValue) {
            if (preg_match('/\{[a-z]+\}/i', $routeValue, $matches)) {
                $paramName = trim($routeValue, '{} ');
                $params[$paramName] = urldecode($urlPieces[$routeKey]);
            } else {
                if ($routeValue != $urlPieces[$routeKey]) {
                    return false;
                }
            }
        }

        $route['params'] = array_merge_recursive($route['params'], $params);

        return $route;
    }

    /**
     * Validate and return a cleansed version from a given route
     * @param array $route
     * @return array|boolean false
     */
    private function isValidRoute(array $route)
    {
        if (!array_key_exists('path', $route)) {
            return false;
        }

        if (!array_key_exists('method', $route)) {
            $route['method'] = 'GET';
        }

        $route['method'] = strtoupper($route['method']);

        if (!array_key_exists('params', $route)) {
            $route['params'] = array();
        }

        return $route;
    }

    /**
     * Validate the HTTP Request Method matches a given route.
     * @param array $route
     * @return boolean
     */
    private function methodMatches(array $route)
    {
        $methods = explode('|', $route['method']);
        if (in_array($this->method, $methods)) {
            return true;
        }

        return false;
    }
}