<?php
namespace Avenue\State;

use Avenue\Interfaces\State\SessionInterface;
use SessionHandlerInterface;

class Session implements SessionInterface
{
    /**
     * CSRF token name.
     *
     * @var string
     */
    const CSRF_TOKEN_NAME = 'csrfToken';

    /**
     * Session database handler class instance.
     *
     * @var mixed
     */
    protected $handler;

    /**
     * App's secret key.
     *
     * @var mixed
     */
    protected $secretKey;

    /**
     * Session class constructor.
     * Register respective handler methods before starting session.
     *
     * @param SessionHandlerInterface $handler
     * @param mixed $secretKey
     * @throws \InvalidArgumentException
     */
    public function __construct(SessionHandlerInterface $handler, $secretKey)
    {
        $this->handler = $handler;
        $this->secretKey = $secretKey;

        // check if secret key is empty
        if (empty(trim($this->secretKey))) {
            throw new \InvalidArgumentException('Secret key must not be empty!');
        }

        $this->prepare()->start();
    }

    /**
     * Set session value.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\SessionInterface::set()
     */
    public function set($key, $value)
    {
        if (empty(trim($key))) {
            throw new \InvalidArgumentException('Session key is missing!');
        }

        $_SESSION[$key] = $value;
    }

    /**
     * Get session value.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\SessionInterface::get()
     */
    public function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return null;
    }

    /**
     * Remove particular session value.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\SessionInterface::remove()
     */
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Remove all sessions.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\SessionInterface::removeAll()
     */
    public function removeAll()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * Regenerate new session ID.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\SessionInterface::regenerateId()
     */
    public function regenerateId()
    {
        if (!headers_sent()) {
            return session_regenerate_id(true);
        }

        return false;
    }

    /**
     * Get session ID.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\SessionInterface::getId()
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Start session.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\SessionInterface::start()
     */
    public function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Generate unique random hashed string for CSRF token.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\SessionInterface::setCsrfToken()
     */
    public function setCsrfToken()
    {
        $csrfToken = hash_hmac('sha256', uniqid(mt_rand()), $this->secretKey);
        $this->set(static::CSRF_TOKEN_NAME, $csrfToken);

        return $csrfToken;
    }

    /**
     * Get the persisted CSRF token value.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\State\SessionInterface::getCsrfToken()
     */
    public function getCsrfToken()
    {
        return $this->get(static::CSRF_TOKEN_NAME);
    }

    /**
     * Runtime and handlers settings before starting the session.
     */
    protected function prepare()
    {
        // set gc max lifetime
        ini_set('session.gc_maxlifetime', intval($this->handler->getConfig('lifetime')));

        // set session cookie accessible via http only
        ini_set('session.cookie_httponly', 1);

        // register respective handler methods
        session_set_save_handler(
            [$this->handler, 'open'],
            [$this->handler, 'close'],
            [$this->handler, 'read'],
            [$this->handler, 'write'],
            [$this->handler, 'destroy'],
            [$this->handler, 'gc']
        );

        return $this;
    }
}