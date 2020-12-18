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
namespace Phavour\Http;

/**
 * Request
 */
class Request
{
    /**
     * @var array
     */
    private $env;

    /**
     * Raw array of all params, including $_GET, $_POST, and user params
     *
     * @var array
     */
    private $params = array ();

    /**
     * The raw $_GET array
     *
     * @var array
     */
    private $get = array ();

    /**
     * The raw $_POST array
     *
     * @var array
     */
    private $post = array ();

    /**
     * Body of response, or false if none set
     *
     * @var string|boolean false
     */
    private $body = false;

    /**
     * Holds any manually set parameters when using:
     * $this->setParam(foo, bar)
     *
     * @var array
     */
    private $userParams = array();

    /**
     * Assigns all $_SERVER and user parameters.
     */
    public function __construct()
    {
        $this->env = $_SERVER;
        $this->buildParams();
    }

    /**
     * Retrieve the REQUEST_URI without and GET parameters
     *
     * @example /contact-us
     * @return string
     */
    public function getRequestUri()
    {
        if (isset($this->env['REQUEST_URI'])) {
            $uri = $this->env['REQUEST_URI'];
        } else {
            $uri = "/";
        }
        if (strstr($uri, "?")) {
            $uri = strstr($uri, "?", true);
        }
        return $uri;
    }

    /**
     * Is request via HTTPS
     *
     * @return boolean
     */
    public function isHttpsRequest()
    {
        if (empty($this->env['HTTPS']) || $this->env['HTTPS'] == "off") {
            return false;
        }

        return true;
    }

    /**
     * Retrieve the REQUEST_URI WITH and GET parameters
     *
     * @example /contact-us
     * @return string
     */
    public function getRawRequestUri()
    {
        if (isset($this->env['REQUEST_URI'])) {
            $uri = $this->env['REQUEST_URI'];
        } else {
            $uri = "/";
        }

        return $uri;
    }

    /**
     * Retrieve a header from the request header stack and
     * optionally set a default value to use if key isn't
     * found.
     *
     * @param string $name
     * @param mixed $default
     * @return string
     */
    public function getHeader($name, $default = null)
    {
        if (empty($name)) {
            return $default;
        }

        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (isset($this->env[$temp])) {
            return $this->env[$temp];
        }

        if (function_exists('apache_request_headers')) {
            // @codeCoverageIgnoreStart
            $method = 'apache_request_headers';
            $headers = $method();
            if (isset($headers[$name])) {
                return $headers[$name];
            }
            $name = strtolower($name);
            foreach ($headers as $key => $value) {
                if (strtolower($key) == $name) {
                    return $value;
                }
            }
        }
        // @codeCoverageIgnoreEnd

        return $default;
    }

    /**
     * Return the REQUEST_METHOD from the SERVER global array
     *
     * @return string
     */
    public function getRequestMethod()
    {
        if (isset($this->env['REQUEST_METHOD'])) {
            $m = $this->env['REQUEST_METHOD'];
        } else {
            $m = "GET";
        }

        return $m;
    }

    /**
     * Add a single parameter to the params stack
     *
     * @param string $name
     * @param mixed $value
     */
    public function setParam($name, $value)
    {
        $this->userParams[$name] = $value;
        $this->buildParams();
    }

    /**
     * Retrieve all request params (GET / POST and Manually Set Params) as a
     * single array
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Retrieve all POST params as an array
     *
     * @return array
     */
    public function getPostParams()
    {
        return $this->post;
    }

    /**
     * Retrieve all GET params as an array
     *
     * @return array
     */
    public function getGetParams()
    {
        return $this->get;
    }

    /**
     * Retrieve all request params (GET and POST) as a single array
     *
     * @param string $name
     * @param mixed|null $default
     * @return array
     */
    public function getParam($name, $default = null)
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }

        return $default;
    }

    /**
     * Check if the request is HTTP_GET
     *
     * @return boolean
     */
    public function isGet()
    {
        return (strtolower($this->getRequestMethod()) == "get");
    }

    /**
     * Check if the request is HTTP_POST
     *
     * @return boolean
     */
    public function isPost()
    {
        return (strtolower($this->getRequestMethod()) == "post");
    }

    /**
     * Check if the request is HTTP_PUT
     *
     * @return boolean
     */
    public function isPut()
    {
        return (strtolower($this->getRequestMethod()) == "put");
    }

    /**
     * Check if the request is HTTP_DELETE
     *
     * @return boolean
     */
    public function isDelete()
    {
        return (strtolower($this->getRequestMethod()) == "delete");
    }

    /**
     * Get the raw body, if any.
     *
     * @return string|boolean false
     */
    public function getBody()
    {
        if ($this->body == false) {
            $body = @file_get_contents('php://input');
            if ($body != false && strlen(trim($body)) > 0) {
                // @codeCoverageIgnoreStart
                $this->body = $body;
            } else {
                // @codeCoverageIgnoreEnd
                $this->body = false;
            }
        }

        if ($this->body == false) {
            return false;
        }

        // @codeCoverageIgnoreStart
        return $this->body;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Alias for self::getBody
     *
     * @see Request::getBody
     * @return string|boolean false
     */
    public function getRawBody()
    {
        return $this->getBody();
    }

    /**
     * Alias for self::getIp
     *
     * @see Request::getIp
     * @return string
     */
    public function getClientIp()
    {
        return $this->getIp();
    }

    /**
     * Return the users IP Address
     *
     * @return string
     */
    public function getIp()
    {
        if (isset($this->env['HTTP_X_FORWARDED_FOR'])) {
            return $this->env['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($this->env['HTTP_CLIENT_IP'])) {
            return $this->env['HTTP_CLIENT_IP'];
        }

        return $this->env['REMOTE_ADDR'];
    }

    /**
     * Assign the GET, POST and User Params to the main
     * Params
     */
    protected function buildParams()
    {
        if (empty($this->get)) {
            foreach (@$_GET as $k => $v) {
                $this->get[$k] = $v;
                $this->params[$k] = $v;
            }
        }

        if (empty($this->post)) {
            foreach (@$_POST as $k => $v) {
                $this->post[$k] = $v;
                $this->params[$k] = $v;
            }
        }

        foreach ($this->userParams as $k => $v) {
            $this->params[$k] = $v;
        }
    }

    /**
     * Is the request an Ajax XMLHttpRequest?
     *
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        if ($this->getHeader('X_REQUESTED_WITH') === 'XMLHttpRequest') {
            return true;
        }

        return false;
    }
}
