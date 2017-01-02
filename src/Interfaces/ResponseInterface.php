<?php
namespace Avenue\Interfaces;

interface ResponseInterface
{
    /**
     * Writing input to response body.
     *
     * @param mixed $input
     */
    public function write($input);

    /**
     * Rendering the response body.
     */
    public function render();

    /**
     * Output the reponse body.
     */
    public function output();

    /**
     * Cleanup the properties once output is rendered.
     */
    public function cleanup();

    /**
     * Retrieving the response body.
     */
    public function getBody();

    /**
     * Set the http status code.
     *
     * @param mixed $code
     */
    public function withStatus($code);

    /**
     * Get the http status code.
     */
    public function getStatusCode();

    /**
     * Get the http status description.
     *
     * @param mixed $code
     */
    public function getStatusDescription($code);

    /**
     * Send http headers.
     */
    public function sendHttpHeaders();

    /**
     * Send defined http headers, if any.
     */
    public function sendDefinedHeaders();

    /**
     * Set the respective http headers.
     *
     * @param array $headers
     */
    public function withHeader(array $headers = []);

    /**
     * Get the specified header based on the key.
     *
     * @param mixed $key
     */
    public function getHeader($key);

    /**
     * Alias method for setting JSON header.
     */
    public function withJsonHeader();

    /**
     * Alias method for setting text header.
     */
    public function withTextHeader();

    /**
     * Alias method for setting CSV header.
     */
    public function withCsvHeader();

    /**
     * Alias method for setting XML header.
     */
    public function withXmlHeader();

    /**
     * Http cache with etag method.
     *
     * @param mixed $uniqueId
     * @param string $type
     */
    public function withEtag($uniqueId, $type = 'strong');

    /**
     * Http cache with last modified by providing UNIX timestamp.
     *
     * @param integer $timestamp
     */
    public function withLastModified($timestamp);

    /**
     * Http cache for expire time as provided.
     *
     * @param mixed $expireTime
     */
    public function cache($expireTime);

    /**
     * Return true or false for cached content.
     */
    public function hasCache();

    /**
     * Convert array to JSON.
     *
     * @param  array  $data
     */
    public function toJson(array $data);
}
