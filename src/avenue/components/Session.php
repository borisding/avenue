<?php
namespace Avenue\Components;

use Avenue\Components\SessionDatabase;

class Session
{
    /**
     * Session database instance.
     * 
     * @var mixed
     */
    protected $ssdb;
    
    /**
     * Session class constructor.
     * 
     * @param SessionDatabase $ssdb
     */
    public function __construct(SessionDatabase $ssdb)
    {
        $this->ssdb = $ssdb;
        $this->assign()->start();
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
        } else {
            return null;
        }
    }
    
    /**
     * Regenerate new session id.
     */
    public function regenerateId()
    {
        session_regenerate_id();
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
     * Set user level session storage functions.
     */
    public function assign()
    {
        session_set_save_handler(
        [$this->ssdb, 'ssopen'],
        [$this->ssdb, 'ssclose'],
        [$this->ssdb, 'ssread'],
        [$this->ssdb, 'sswrite'],
        [$this->ssdb, 'ssdestroy'],
        [$this->ssdb, 'ssgc']
        );
        
        return $this;
    }
}