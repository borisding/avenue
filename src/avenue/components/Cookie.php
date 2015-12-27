<?php
namespace Avenue\Components;

use Avenue\App;
use Avenue\Components\Encryption;

class Cookie
{
    /**
     * App class instance.
     * 
     * @var mixed
     */
    protected $app;
    
    /**
     * Encryption class instance.
     * 
     * @var mixed
     */
    protected $encryption;
    
    /**
     * Default cookie configuration.
     * 
     * @var array
     */
    protected $config = [
        // cookie's expiration, default 20 min
        'expire' => 0,
        // cookie's path that is available
        'path' => '',
        // domain where cookie is available
        'domain' => '',
        // only transmitted on https
        'secure' => false,
        // only for http protocol, not allowed for javascript
        'httpOnly' => true,
        // encrypt cookie's value
        'encrypt' => false
    ];
    
    /**
     * Maximum size of cookie value, 4KB
     *
     * @var integer
     */
    const MAX_SIZE = 4096;
    
    /**
     * Cookie class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->config = array_merge($this->config, $this->app->getConfig('cookie'));
        
        // get the encryption instance if 'encrypt' set as true
        if ($this->config['encrypt']) {
            $this->encryption = $this->app->encryption();
        }
    }
    
    /**
     * Set cookie value based on the assigned key.
     * 
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value = null)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Key must not be empty!');
        }
        
        $value = $this->encrypt($value);
        $valueLen = $this->app->hasFunction('mb_strlen') ? mb_strlen($value) : strlen($value);
        
        if ($valueLen > static::MAX_SIZE) {
            throw new \InvalidArgumentException('Saving content aborted! Session cookie data is larger than 4KB.');
        }
        
        // extract respective config keys as parameters
        extract($this->config);
        setcookie($key, $value, time() + $expire, $path, $domain, $secure, $httpOnly);
        
        // for immediate cookie assignment
        $_COOKIE[$key] = $value;
    }
    
    /**
     * Get the plain text of cookie value.
     * 
     * @param mixed $key
     * @return mixed|NULL
     */
    public function get($key)
    {
        if (isset($_COOKIE[$key])) {
            return $this->decrypt($_COOKIE[$key]);
        }
        
        return '';
    }
    
    /**
     * Remove specific cookie value based on the key.
     * 
     * @param mixed $key
     */
    public function remove($key)
    {
        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
            setcookie($key, '', time() - 604800, $this->config['path'], $this->config['domain']);
        }
    }
    
    /**
     * Remove cookie values, respectively.
     */
    public function removeAll()
    {
        foreach ($_COOKIE as $key => $value) {
            $this->remove($key);
        }
    }
    
    /**
     * Get the encrypted data.
     * 
     * @param mixed $plaintext
     */
    protected function encrypt($plaintext)
    {
        if ($this->encryption instanceof Encryption) {
            return $this->encryption->set($plaintext);
        }
        
        return $plaintext;
    }
    
    /**
     * Decrypt the data.
     * 
     * @param mixed $data
     */
    protected function decrypt($data)
    {
        if ($this->encryption instanceof Encryption) {
            return $this->encryption->get($data);
        }
        
        return $data;
    }
}