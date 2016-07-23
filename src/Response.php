<?php
namespace Avenue;

use Avenue\App;
use Avenue\Interfaces\ResponseInterface;

class Response implements ResponseInterface
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
     * Flag for cached content.
     *
     * @var boolean
     */
    protected $boolCache;

    /**
     * Response class constructor.
     * Set the default status code and content type.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->boolCache = false;
        $this->http = $this->app->getHttpVersion();

        $this->withStatus(200);
        $this->withHeader(['Content-Type' => 'text/html']);
    }

    /**
     * Print out the content and cleanup if no cache.
     * Else, just clean and exit.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::render()
     */
    public function render()
    {
        if (!headers_sent()) {
            $this->sendHttpHeader()->sendDefinedHeaders();
        }

        // clear body and exit for cache
        if ($this->hasCache()) {
            $this->cleanup();
            exit(0);
        }

        $this->output()->cleanup();
    }

    /**
     * Sending http header.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::sendHttpHeader()
     */
    public function sendHttpHeader()
    {
        $statusCode = $this->getStatusCode();
        $statusDesc = $this->getStatusDesc($statusCode);
        $httpProtocol = 'HTTP/' . (!empty($this->http) ? $this->http : '1.1');

        header(sprintf('%s %d %s', $httpProtocol, $statusCode, $statusDesc), true, $statusCode);
        unset($statusCode, $statusDesc, $httpProtocol);

        return $this;
    }

    /**
     * Sending the the defined header, if any.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::sendDefinedHeaders()
     */
    public function sendDefinedHeaders()
    {
        if (!$this->hasCache()) {
            header(sprintf('Content-Length: %d', strlen($this->getBody())));
        }

        foreach ($this->headers as $type => $format) {
            header($type . ': ' . $format, false);
        }

        return $this;
    }

    /**
     * Return true if has cache.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::isCached()
     */
    public function hasCache()
    {
        return $this->boolCache;
    }

    /**
     * Writing string input to response body.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::write()
     */
    public function write($input)
    {
        // just "cast" array or object via serialize if accidentally written
        if (is_array($input) || is_object($input)) {
            $input = serialize($input);
        }

        $this->body .= $input;
        return $this;
    }

    /**
     * Set the content length and print the body output.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::output()
     */
    public function output()
    {
        echo $this->getBody();
        return $this;
    }

    /**
     * Reset properties.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::cleanup()
     */
    public function cleanup()
    {
        $this->statusCode = '';
        $this->body = '';
        $this->headers = [];

        return $this;
    }

    /**
     * Get the body content.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::getBody()
     */
    public function getBody()
    {
        return (string)$this->body;
    }

    /**
     * Set the http status code.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::withStatus()
     */
    public function withStatus($code)
    {
        return $this->statusCode = $code;
    }

    /**
     * Get the http status code.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::getStatusCode()
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the status description based on the status code.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::getStatusDesc()
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
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::withHeader()
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
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::getHeader()
     */
    public function getHeader($key)
    {
        return $this->app->arrGet($key, $this->headers);
    }

    /**
     * Shortcut for response with JSON header.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::withJsonHeader()
     */
    public function withJsonHeader()
    {
        return $this->withHeader(['Content-Type' => 'application/json']);
    }

    /**
     * Shortcut for response with text header.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::withTextHeader()
     */
    public function withTextHeader()
    {
        return $this->withHeader(['Content-Type' => 'text/plain']);
    }

    /**
     * Shortcut for response with CSV header.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::withCsvHeader()
     */
    public function withCsvHeader()
    {
        return $this->withHeader(['Content-Type' => 'text/csv']);
    }

    /**
     * Shortcut for response with XML header.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::withXmlHeader()
     */
    public function withXmlHeader()
    {
        return $this->withHeader(['Content-Type' => 'text/xml']);
    }

    /**
     * HTTP caching with ETag method.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::withEtag()
     */
    public function withEtag($uniqueId, $type = 'strong')
    {
        $arrTypes = ['strong', 'weak'];

        if (!in_array($type, $arrTypes)) {
            throw new \InvalidArgumentException('Invalid type of ETag! Type: [strong] or [weak].');
        }

        // for weak type
        if ($type === $arrTypes[1]) {
            $uniqueId = 'W/' . $uniqueId;
        }

        $this->withHeader(['ETag' => $uniqueId]);
        $HTTP_IF_NONE_MATCH = $this->app->request->getHeader('If-None-Match');

        if ($HTTP_IF_NONE_MATCH && $HTTP_IF_NONE_MATCH === $uniqueId) {
            $this->setCacheStatus();
        }

        return $this;
    }

    /**
     * Http cache with last modified by providing UNIX timestamp.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ResponseInterface::withLastModified()
     */
    public function withLastModified($timestamp)
    {
        $this->withHeader(['Last-Modified' => $this->getGmtDateTime($timestamp)]);
        $HTTP_IF_MODIFIED_SINCE = $this->app->request->getHeader('If-Modified-Since');

        if (strtotime($HTTP_IF_MODIFIED_SINCE) === $timestamp) {
            $this->setCacheStatus();
        }

        return $this;
    }

    /**
     * Http cache for expire time as provided.
     * Can be chained either with withEtag or withLastModified method.
     *
     * @param mixed $expireTime
     */
    public function cache($expireTime)
    {
        // parse string using strtotime if string provided
        if (is_string($expireTime)) {
            $expireTime = strtotime($expireTime);
        }

        // set cache control and expires headers
        $this->withHeader([
            'Cache-Control' => 'max-age=' . $expireTime,
            'Expires' => $this->getGmtDateTime($expireTime)
        ]);

        return $this;
    }

    /**
     * Get the GMT date/time based on the timestamp.
     *
     * @param integer $timestamp
     * @throws \InvalidArgumentException
     */
    protected function getGmtDateTime($timestamp)
    {
        if (!is_integer($timestamp)) {
            throw new \InvalidArgumentException('Invalid data type of UNIX timestamp! Expect integer value.');
        }

        return gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
    }

    /**
     * Set the cache status.
     *
     * @return \Avenue\Response
     */
    protected function setCacheStatus()
    {
        $this->boolCache = true;
        $this->withStatus(304);

        return $this;
    }
}