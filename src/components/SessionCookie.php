<?php
namespace Avenue\Components;

use Avenue\App;
use Avenue\Components\Cookie;
use SessionHandlerInterface;

class SessionCookie implements SessionHandlerInterface
{
    /**
     * App class instance.
     * 
     * @var mixed
     */
    protected $app;
    
    /**
     * Cookie instance.
     * 
     * @var mixed
     */
    protected $cookie;
    
    /**
     * Name for session cookie.
     * 
     * @var string
     */
    const COOKIE_NAME = 'AVENUESESSID';
    
    /**
     * Session cookie class constructor.
     * 
     * @param App $app
     * @param Cookie $cookie
     */
    public function __construct(App $app, Cookie $cookie)
    {
        $this->app = $app;
        $this->cookie = $cookie;
    }
    
    /**
     * Invoked when session is being opened.
     * 
     * @see SessionHandlerInterface::open()
     */
    public function open($path, $name)
    {
        return true;
    }
    
    /**
     * Invoked once session is written.
     * 
     * @see SessionHandlerInterface::close()
     */
    public function close()
    {
        session_write_close();
        return true;
    }
    
    /**
     * Retrieving the serialized session data inserted previously.
     * 
     * @see SessionHandlerInterface::read()
     */
    public function read($id)
    {
        return $this->cookie->get(static::COOKIE_NAME);
    }
    
    /**
     * Writing session values into cookie.
     * 
     * @see SessionHandlerInterface::write()
     */
    public function write($id, $value)
    {
        $this->cookie->set(static::COOKIE_NAME, $value);
        return true;
    }
    
    /**
     * Invoked once session is destroyed.
     * 
     * @see SessionHandlerInterface::destroy()
     */
    public function destroy($id)
    {
        $this->cookie->remove(static::COOKIE_NAME);
        return true;
    }
    
    /**
     * Garbage collection to clean up old data by removing it.
     * 
     * @see SessionHandlerInterface::gc()
     */
    public function gc($lifetime)
    {
        return true;
    }
}