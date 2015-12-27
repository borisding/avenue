<?php
namespace Avenue\Components;

use SessionHandlerInterface;

class Session
{
    /**
     * Session database instance.
     * 
     * @var mixed
     */
    protected $handler;
    
    /**
     * Session config
     * 
     * @var array
     */
    protected $config = [];
    
    /**
     * Session class constructor.
     * 
     * @param SessionHandlerInterface $handler
     * @param array $config
     */
    public function __construct(SessionHandlerInterface $handler, array $config = [])
    {
        $this->handler = $handler;
        $this->config = $config;
        $this->ready()->start();
    }
    
    /**
     * Set session value based on the assigned key.
     * 
     * @param mixed $key
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function set($key, $value)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Key must not be empty!');
        }
        
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get the session value based on the key.
     * 
     * @param mixed $key
     * @return mixed|NULL
     */
    public function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        
        return '';
    }
    
    /**
     * Remove sepcific session based on the key.
     * 
     * @param mixed $key
     */
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Remove all defined session variables.
     */
    public function removeAll()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }
    
    /**
     * Regenerate new session id.
     * 
     * @return boolean
     */
    public function regenerateId()
    {
        if (!headers_sent()) {
            return session_regenerate_id();
        }
        
        return false;
    }
    
    /**
     * Get session ID.
     * 
     * @return string
     */
    public function getId()
    {
        return session_id();
    }
    
    /**
     * Start the session if not started.
     */
    public function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Generate unique random hashed string for csrf token.
     * Then persisted by assigning to session.
     * 
     * @return string
     */
    public function getCsrfToken()
    {
        $csrfToken = hash('sha256', uniqid(mt_rand()));
        $this->set('csrfToken', $csrfToken);
        
        return $csrfToken;
    }
    
    /**
     * Setting before start the session.
     */
    protected function ready()
    {
        // set the gc maxlifetime
        ini_set('session.gc_maxlifetime', intval($this->config['lifetime']));
        
        // register write close when shutting down
        register_shutdown_function('session_write_close');
        
        // register respective handlers
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