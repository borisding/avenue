<?php
namespace Avenue\State;

use Avenue\App;
use Avenue\Crypt;
use Avenue\Interfaces\State\CookieInterface;

class Cookie implements CookieInterface
{
    /**
     * App class instance.
     *
     * @var mixed
     */
    protected $app;

    /**
     * App's secret key.
     *
     * @var mixed
     */
    protected $secretKey;

    /**
     * Crypt class instance.
     *
     * @var mixed
     */
    protected $crypt;

    /**
     * Default cookie configuration.
     *
     * @var array
     */
    protected $config = [
        'expire' => 0,
        'path' => '',
        'domain' => '',
        'secure' => false,
        'httpOnly' => false,
        'encrypt' => false
    ];

    /**
     * Maximum size of cookie value, 4KB
     *
     * @var integer
     */
    const MAX_SIZE = 4096;

    /**
     * Delimiter string.
     *
     * @var string
     */
    const DELIMITER = '||';

    /**
     * Cookie class constructor.
     *
     * @param App $app
     * @param array $config
     * @throws \InvalidArgumentException
     */
    public function __construct(App $app, array $config = [])
    {
        $this->app = $app;
        $this->config = array_merge($this->config, $config);
        $this->secretKey = $this->app->getSecretKey();

        // get the crypt instance if 'encrypt' set as true
        if ($this->config['encrypt']) {
            $this->crypt = $this->app->crypt();
        }

        // check if secret key is empty
        if (empty(trim($this->secretKey))) {
            throw new \InvalidArgumentException('Secret key must not be empty!');
        }
    }

    /**
     * Set cookie value based on the assigned key.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\CookieInterface::set()
     */
    public function set($key, $value = null)
    {
        if (empty(trim($key))) {
            throw new \InvalidArgumentException('Cookie key is missing!');
        }

        $value = $this->encrypt($value);
        $value = $this->hashing($key, $value) . static::DELIMITER . $value;

        if (mb_strlen($value) > static::MAX_SIZE) {
            throw new \InvalidArgumentException('Saving content aborted! Cookie data is larger than 4KB.');
        }

        // extract respective config keys as parameters
        extract($this->config);

        // set cookie with respective attributes
        setcookie($key, $value, time() + $expire, $path, $domain, $secure, $httpOnly);

        // for immediate cookie assignment
        $_COOKIE[$key] = $value;
    }

    /**
     * Get the plain text of cookie value.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\CookieInterface::get()
     */
    public function get($key)
    {
        if (isset($_COOKIE[$key])) {
            $value = $this->verify($key, $_COOKIE[$key]);
            $value = $this->decrypt($value);

            return $value;
        }

        return null;
    }

    /**
     * Remove specific cookie value based on the key.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\CookieInterface::remove()
     */
    public function remove($key)
    {
        setcookie($key, '', time() - 3600, $this->config['path'], $this->config['domain']);
        unset($_COOKIE[$key]);
    }

    /**
     * Remove cookie values, respectively.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\CookieInterface::removeAll()
     */
    public function removeAll()
    {
        foreach ($_COOKIE as $key => $value) {
            $this->remove($key);
        }
    }

    /**
     * Create cookie signature by hashing value with key and secret.
     *
     * @param mixed $key
     * @param mixed $value
     * @return string
     */
    protected function hashing($key, $value)
    {
        return hash_hmac('sha256', $value . $key . $this->secretKey, $this->secretKey);
    }

    /**
     * Verify cookie signature and return value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    protected function verify($key, $value)
    {
        if (strpos($value, static::DELIMITER) !== false) {
            list($hashed, $value) = explode(static::DELIMITER, $value, 2);

            // return cookie value if signature is valid
            if ($this->app->hashedCompare($this->hashing($key, $value), $hashed)) {
                return $value;
            }
        }

        // remove the tempered cookie if not valid
        $this->remove($key);
        return null;
    }

    /**
     * Get the encrypted data.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function encrypt($value)
    {
        if ($this->crypt instanceof Crypt) {
            return $this->crypt->encrypt($value);
        }

        return $value;
    }

    /**
     * Decrypt the data.
     *
     * @param mixed $value
     */
    protected function decrypt($value)
    {
        if ($this->crypt instanceof Crypt) {
            return $this->crypt->decrypt($value);
        }

        return $value;
    }
}