<?php
namespace Avenue;

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
    protected $httpVersion;

    /**
     * Response class constructor.
     * Set the default status code and content type.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->httpVersion = $this->app->config('httpVersion');
        $this->setHttpStatus(200);
        $this->setHttpHeader(['Content-Type' => 'text/html']);
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
        $statusCode = $this->getHttpStatus();
        $statusDesc = $this->getHttpStatusDesc($statusCode);
        $httpProtocol = 'HTTP/' . (!empty($this->httpVersion) ? $this->httpVersion : '1.1');

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
    public function setHttpStatus($code)
    {
        return $this->statusCode = $code;
    }

    /**
     * Get the http status code.
     *
     * @return mixed
     */
    public function getHttpStatus()
    {
        return $this->statusCode;
    }

    /**
     * Returning the status based on the status code.
     *
     * @param mixed $code
     * @return mixed
     */
    public function getHttpStatusDesc($code)
    {
        $httpStatusCodes = require_once AVENUE_SRC_DIR . '/includes/http_status.php';
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
    public function setHttpHeader(array $headers = [])
    {
        foreach($headers as $type => $format) {
            $this->headers[$type] = $format;
        }

        return $this;
    }
    
    /**
     * Get the header description based on the key.
     * 
     * @param mixed $key
     */
    public function getHttpHeader($key)
    {
        return $this->app->arrGet($key, $this->headers);
    }
    
    /**
     * Shortcut for JSON header.
     * 
     * @return \Avenue\Response
     */
    public function jsonHeader()
    {
        return $this->setHttpHeader(['Content-Type' => 'application/json']);
    }
    
    /**
     * Shortcut for text header.
     * 
     * @return \Avenue\Response
     */
    public function textHeader()
    {
        return $this->setHttpHeader(['Content-Type' => 'text/plain']);
    }

    /**
     * Shortcut for csv header.
     * 
     * @return \Avenue\Response
     */
    public function csvHeader()
    {
        return $this->setHttpHeader(['Content-Type' => 'text/csv']);
    }
    
    /**
     * Shortcut for xml header.
     * 
     * @return \Avenue\Response
     */
    public function xmlHeader()
    {
        return $this->setHttpHeader(['Content-Type' => 'text/xml']);
    }
}