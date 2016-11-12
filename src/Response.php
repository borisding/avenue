<?php
namespace Avenue;

use Avenue\App;
use Avenue\Interfaces\ResponseInterface;

class Response implements ResponseInterface
{
    /**
     * Avenue class instance.
     *
     * @var \Avenue\App
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
     * Flag for cached content.
     *
     * @var boolean
     */
    protected $boolCache = false;

    /**
     * Http status code.
     *
     * @var mixed
     */
    protected $statusCode = 200;

    /**
     * List of status descriptions.
     *
     * @var array
     */
    protected $statusDescriptions = [];

    /**
     * Response class constructor.
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        // store the status descriptions if empty
        if (empty($this->statusDescriptions)) {
            $this->statusDescriptions = require __DIR__ . '/_includes/http_status.php';
        }
    }

    /**
     * Print out the content and cleanup if no cache.
     * Otherwise, just cleanup and exit.
     *
     * @return mixed
     */
    public function render()
    {
        if (!headers_sent()) {
            $this->sendHttpHeaders()->sendDefinedHeaders();
        }

        // clear body and exit for cache
        if ($this->hasCache()) {
            $this->cleanup();
            exit(0);
        }

        $this->output()->cleanup();
    }

    /**
     * Sending http header with defined http attributes.
     *
     * @return \Avenue\Response
     */
    public function sendHttpHeaders()
    {
        $statusCode = $this->getStatusCode();
        $statusDescription = $this->getStatusDescription($statusCode);

        $httpVersion = $this->app->getHttpVersion();
        $httpProtocol = sprintf('HTTP/%s', (!empty($httpVersion) ? $httpVersion : '1.1'));

        $body = $this->getBody();

        if (is_int($statusCode)) {
            header(sprintf('%s %d %s', $httpProtocol, $statusCode, $statusDescription), true, $statusCode);
        }

        if (!$this->hasCache() && !empty($body)) {
            header(sprintf('Content-Length: %d', strlen($body)));
        }

        unset($statusCode, $statusDescription, $httpProtocol, $body);
        return $this;
    }

    /**
     * Sending the the defined headers, if any.
     * Allow multiple headers with same type.
     *
     * @return \Avenue\Response
     */
    public function sendDefinedHeaders()
    {
        foreach ($this->headers as $type => $format) {

            // processed as multiple headers for the same type
            // when format is provided as index based array
            if ($this->app->arrIsIndex($format)) {

                foreach ($format as $value) {
                    header($type . ': ' . $value, false);
                }
            } else {
                header($type . ': ' . $format);
            }
        }

        return $this;
    }

    /**
     * Return true if has cache.
     *
     * @return boolean
     */
    public function hasCache()
    {
        return $this->boolCache;
    }

    /**
     * Writing string input to response body.
     *
     * @param  mixed $input
     * @return \Avenue\Response
     */
    public function write($input)
    {
        // just 'cast' array or object via serialize if accidentally written
        if (is_array($input) || is_object($input)) {
            $input = serialize($input);
        }

        $this->body .= $input;
        return $this;
    }

    /**
     * Set the content length and print the body output.
     *
     * @return \Avenue\Response
     */
    public function output()
    {
        echo $this->getBody();
        return $this;
    }

    /**
     * Reset response properties to default.
     *
     * @return \Avenue\Response
     */
    public function cleanup()
    {
        $this->headers = [];
        $this->body = '';
        $this->boolCache = false;
        $this->statusCode = 200;

        return $this;
    }

    /**
     * Get the response body.
     *
     * @return string
     */
    public function getBody()
    {
        return (string)$this->body;
    }

    /**
     * Set the http status code.
     *
     * @param  mixed $code
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
     * Get the status description based on the status code.
     *
     * @param  mixed $code
     * @return mixed
     */
    public function getStatusDescription($code)
    {
        return $this->app->arrGet($code, $this->statusDescriptions, 'Unknown http status!');
    }

    /**
     * Set the respective http headers, if any.
     *
     * @param  array $headers
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
     * @param  mixed $key
     * @return mixed
     */
    public function getHeader($key)
    {
        return $this->app->arrGet($key, $this->headers);
    }

    /**
     * Shortcut for response with JSON header.
     *
     * @return mixed
     */
    public function withJsonHeader()
    {
        return $this->withHeader(['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * Shortcut for response with text header.
     *
     * @return mixed
     */
    public function withTextHeader()
    {
        return $this->withHeader(['Content-Type' => 'text/plain; charset=utf-8']);
    }

    /**
     * Shortcut for response with CSV header.
     *
     * @return mixed
     */
    public function withCsvHeader()
    {
        return $this->withHeader(['Content-Type' => 'text/csv; charset=utf-8']);
    }

    /**
     * Shortcut for response with XML header.
     *
     * @return mixed
     */
    public function withXmlHeader()
    {
        return $this->withHeader(['Content-Type' => 'text/xml; charset=utf-8']);
    }

    /**
     * HTTP caching with ETag method.
     *
     * @param  mixed $uniqueId
     * @param  string $type
     * @return \Avenue\Response
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
        $httpIfNoneMatch = $this->app->request()->getHeader('If-None-Match');

        if ($httpIfNoneMatch && $httpIfNoneMatch === $uniqueId) {
            $this->setCacheStatus();
        }

        return $this;
    }

    /**
     * Http caching with last modified by providing UNIX timestamp.
     *
     * @param  mixed $timestamp
     * @return \Avenue\Response
     */
    public function withLastModified($timestamp)
    {
        $this->withHeader(['Last-Modified' => $this->getGmtDateTime($timestamp)]);
        $httpIfModifiedSince = $this->app->request()->getHeader('If-Modified-Since');

        if (strtotime($httpIfModifiedSince) === $timestamp) {
            $this->setCacheStatus();
        }

        return $this;
    }

    /**
     * Http caching for expire time as provided.
     * Can be chained either with withEtag or withLastModified method.
     *
     * @param  mixed $expireTime
     * @return \Avenue\Response
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
     * @param  mixed $timestamp
     * @return string
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
