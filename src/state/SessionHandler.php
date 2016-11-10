<?php
namespace Avenue\State;

use Avenue\App;
use Avenue\Crypt;
use Avenue\Interfaces\State\SessionHandlerInterface;

class SessionHandler implements SessionHandlerInterface
{
    /**
     * App class instance.
     *
     * @var \Avenue\App
     */
    protected $app;

    /**
     * Crypt class instance.
     *
     * @var \Avenue\Crypt
     */
    protected $crypt;

    /**
     * Default session configuration
     *
     * @var array
     */
    protected $config = [
        'table' => 'session',
        'lifetime' => 0,
        'readSlave' => false,
        'encrypt' => false
    ];

    /**
     * Session handler class constructor.
     *
     * @param App $app
     * @param array $config
     */
    protected function __construct(App $app, array $config = [])
    {
        $this->app = $app;
        $this->config = array_merge($this->config, $config);

        // get the crypt instance if 'encrypt' set as true
        if ($this->getConfig('encrypt')) {
            $this->crypt = $this->app->crypt();
        }
    }

    /**
     * Return the app's secret as configured.
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function getAppSecret()
    {
        $secret = $this->app->getSecret();

        if (empty(trim($secret))) {
            throw new \InvalidArgumentException('Secret must not be empty!');
        }

        return $secret;
    }

    /**
     * Return session specific config based on the name.
     * Giving all session config instead if name is not provided.
     *
     * @param mixed $name
     */
    public function getConfig($name = null)
    {
        if (empty($name)) {
            return $this->config;
        }

        return $this->app->arrGet($name, $this->config);
    }

    /**
     * Get the encrypted session value.
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
     * Decrypt the session value.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function decrypt($value)
    {
        if ($this->crypt instanceof Crypt) {
            return $this->crypt->decrypt($value);
        }

        return $value;
    }
}