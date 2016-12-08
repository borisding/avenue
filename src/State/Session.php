<?php
namespace Avenue\State;

use SessionHandlerInterface;
use Avenue\Interfaces\State\SessionInterface;
use Avenue\State\SessionHandler;

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
     * @var \Avenue\State\SessionHandler
     */
    protected $handler;

    /**
     * Session class constructor.
     * Register respective handler methods before starting session.
     *
     * @param SessionHandlerInterface $handler
     */
    public function __construct(SessionHandlerInterface $handler)
    {
        $this->handler = $handler;
        $this->setup()->start();
    }

    /**
     * Set session value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        if (empty(trim($key))) {
            throw new \InvalidArgumentException('Session key is missing!');
        }

        $_SESSION[$key] = $value;
    }

    /**
     * Get session value based on the provided key.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return null;
    }

    /**
     * Remove particular session value based on the provided key.
     *
     * @param  mixed $key
     */
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Remove all sessions.
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
     * @return boolean
     */
    public function regenerateId()
    {
        if (!headers_sent()) {
            return session_regenerate_id(true);
        }

        return false;
    }

    /**
     * Get current session ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Start new session.
     */
    public function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            return session_start();
        }
    }
    
    /**
     * Generate unique random hashed string for CSRF token.
     *
     * @return mixed
     */
    public function setCsrfToken()
    {
        $csrfToken = hash_hmac('sha256', uniqid(mt_rand()), $this->handler->getAppSecret());
        $this->set($this->getCsrfTokenName(), $csrfToken);

        return $csrfToken;
    }

    /**
     * Get the persisted CSRF token value.
     *
     * @return mixed
     */
    public function getCsrfToken()
    {
        return $this->get($this->getCsrfTokenName());
    }

    /**
     * Return the CSRF token's name.
     *
     * @return mixed
     */
    protected function getCsrfTokenName()
    {
        return static::CSRF_TOKEN_NAME;
    }

    /**
     * Runtime and handlers settings before starting the session.
     *
     * @return \Avenue\State\Session
     */
    protected function setup()
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
