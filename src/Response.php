<?php
namespace Avenue;

use Avenue\App;

class Response
{
    /**
     * Avenue class instance.
     *
     * @var mixed
     */
    protected $app;

    /**
     * Response header.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Response body.
     *
     * @var mixed
     */
    protected $body;

    /**
     * Http status code.
     *
     * @var mixed
     */
    protected $statusCode;

    /**
     * Http version to be used.
     *
     * @var mixed
     */
    protected $http;

    /**
     * Response class constructor.
     * Set the default status code and content type.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->http = $this->app->getHttpVersion();
        $this->withStatus(200);
        $this->withHeader(['Content-Type' => 'text/html']);
    }

    /**
     * Writing input to response body.
     *
     * @param mixed $input
     */
    public function write($input)
    {
        if (empty($this->body)) {
            $this->body = $input;
        } else {
            $this->body .= $input;
        }
    }

    /**
     * Print out the cached content.
     * Reset to the default values.
     */
    public function render()
    {
        if (!headers_sent()) {
            $this->sendHeader()->sendDefinedHeader();
        }

        $this->output()->flush();
    }

    /**
     * Sending header and http status.
     */
    protected function sendHeader()
    {
        $statusCode = $this->getStatusCode();
        $statusDesc = $this->getStatusDesc($statusCode);
        $httpProtocol = 'HTTP/' . (!empty($this->http) ? $this->http : '1.1');

        if (strpos(php_sapi_name(), 'cgi') !== false) {
            header(sprintf('Status: %d %s', $statusCode, $statusDesc), true);
        } else {
            header(sprintf('%s %d %s', $httpProtocol, $statusCode, $statusDesc), true, $statusCode);
        }

        unset($statusCode, $statusDesc, $httpProtocol);
        return $this;
    }

    /**
     * Sending the user defined header, if any.
     */
    protected function sendDefinedHeader()
    {
        foreach ($this->headers as $type => $format) {
            header($type . ': ' . $format, false);
        }

        return $this;
    }

    /**
     * Print the body output.
     */
    protected function output()
    {
        echo $this->getBody();
        return $this;
    }

    /**
     * Reset to the default values after body output printed.
     */
    public function flush()
    {
        $this->statusCode = null;
        $this->body = null;
        $this->headers = [];
    }

    /**
     * Returning the body content.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the http status code.
     *
     * @param mixed $code
     * @return mixed
     */
    public function withStatus($code)
    {
        return $this->statusCode = $code;
    }

    /**
     * Get the http status code.
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returning the status based on the status code.
     *
     * @param mixed $code
     * @return mixed
     */
    public function getStatusDesc($code)
    {
        $httpStatusCodes = require_once __DIR__ . '/includes/http_status.php';
        $httpStatusDesc = 'Unknown http status!';

        if (isset($httpStatusCodes[$code])) {
            $httpStatusDesc = $httpStatusCodes[$code];
        }

        return $httpStatusDesc;
    }

    /**
     * Set the respective http headers, if any.
     *
     * @param array $headers
     * @return \Avenue\Response
     */
    public function withHeader(array $headers = [])
    {
        foreach ($headers as $type => $format) {
            $this->headers[$type] = $format;
        }

        return $this;
    }

    /**
     * Get the header description based on the key.
     *
     * @param mixed $key
     */
    public function getHeader($key)
    {
        return $this->app->arrGet($key, $this->headers);
    }

    /**
     * Shortcut for response with JSON header.
     *
     * @return \Avenue\Response
     */
    public function withJsonHeader()
    {
        return $this->withHeader(['Content-Type' => 'application/json']);
    }

    /**
     * Shortcut for response with text header.
     *
     * @return \Avenue\Response
     */
    public function withTextHeader()
    {
        return $this->withHeader(['Content-Type' => 'text/plain']);
    }

    /**
     * Shortcut for response with CSV header.
     *
     * @return \Avenue\Response
     */
    public function withCsvHeader()
    {
        return $this->withHeader(['Content-Type' => 'text/csv']);
    }

    /**
     * Shortcut for response with XML header.
     *
     * @return \Avenue\Response
     */
    public function withXmlHeader()
    {
        return $this->withHeader(['Content-Type' => 'text/xml']);
    }
}