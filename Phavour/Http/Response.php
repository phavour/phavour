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
 * Response
 */
class Response
{
    /**
     * @var array
     */
    private $headers = array();

    /**
     * @var string
     */
    private $body = '';

    /**
     * @var integer
     */
    private $status = 200;

    public function __construct()
    {
    }

    /**
     * Get the response headers
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the response body
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the response status code
     * @return the $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the headers
     * @param array $headers
     * @param boolean $override (optional) default true
     * @return Response
     */
    public function setHeaders($headers, $override = true)
    {
        if ($override == false) {
            $this->headers = array_merge($headers, $this->headers);
        } else {
            $this->headers = $headers;
        }

        return $this;
    }

    /**
     * Set a single response header
     * @param string $name
     * @param string $value
     * @param boolean $override (optional) default true
     * @return \Phavour\Http\Response
     */
    public function setHeader($name, $value, $override = true)
    {
        if ($override == false) {
            if (array_key_exists($name, $this->headers)) {
                return $this;
            }
        }

        $this->headers[$name] = $value;
    }

    /**
     * Set the body
     * @param string $body
     * @return Response
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set the response status
     * @param integer $status
     * @return Response
     */
    public function setStatus($status)
    {
        $this->status = (int)$status;

        return $this;
    }

    public function sendResponse()
    {
        @ob_clean();
        $this->sendHeaders();
        echo $this->body;
    }

    /**
     * Clear all pending headers
     */
    public function cleanHeaders()
    {
        /* @codeCoverageIgnoreStart() */
        @header_remove();
        /* @codeCoverageIgnoreEnd */
    }

    public function redirect($url, $status = 302)
    {
        $this->status = $status;
        if (!$this->isValidRedirectStatus()) {
            throw new \Exception('Invalid redirect status specified');
        }
        $codes = $this->getHttpResponseCodes();
        $this->setHeader('Location', $url);
        $this->cleanHeaders();
        header('HTTP/1.0 ' . $this->status . ' ' . $codes[$this->status]);
        $this->outputHeaders();
        return;
    }

    /**
     * Send the HTTP Response Headers
     */
    public function sendHeaders()
    {
        $codes = $this->getHttpResponseCodes();
        if (!array_key_exists($this->status, $codes)) {
            $this->status = 500;
            $string = 'HTTP/1.0 500 ' . $codes[500];
        } else {
            if ($this->isValidRedirectStatus()) {
                throw new \Exception(
                    'You cannot send a redirect using a regular response. Use $this->response->redirect(url, status);'
                );
            }
            $string = 'HTTP/1.0 ' . $this->status . ' ' . $codes[$this->status];
        }

        header($string);
        $this->outputHeaders();
    }

    private function outputHeaders()
    {
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
    }

    private function isValidRedirectStatus()
    {
        $redirects = array(301, 302, 307);
        if (!in_array($this->status, $redirects)) {
            return false;
        }

        return true;
    }

    /**
     * Get an array of HTTP Response Codes
     * @return array
     */
    protected function getHttpResponseCodes() {
        return array (
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        );
    }
}
