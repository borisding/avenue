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
        // cookie's expiration
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
        'encrypt' => false,
        // secret key for cookie signature
        'secret' => ''
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
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->config = array_merge($this->config, $this->app->getConfig('cookie'));
        
        // get the encryption instance if 'encrypt' set as true
        if ($this->config['encrypt']) {
            $this->encryption = $this->app->encryption();
        }
        
        // check if secret key is empty
        if (empty(trim($this->config['secret']))) {
            throw new \InvalidArgumentException('Secret must not be empty!');
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
        if (empty(trim($key))) {
            throw new \InvalidArgumentException('The key must not be empty!');
        }
        
        $value = $this->encrypt($value);
        $value = $this->hashing($key, $value) . static::DELIMITER . $value;
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        
        if ($length > static::MAX_SIZE) {
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
            $value = $this->verify($key, $_COOKIE[$key]);
            $value = $this->decrypt($value);
            
            return $value;
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
        unset($_COOKIE[$key]);
        setcookie($key, '', time() - 604800, $this->config['path'], $this->config['domain']);
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
     * Create cookie signature by hashing value with key and secret.
     * 
     * @param mixed $key
     * @param mixed $value
     */
    protected function hashing($key, $value)
    {
        $secret = $this->config['secret'];
        $hashed = hash_hmac('sha1', $value . $key . $secret, $secret);
        
        return $hashed;
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
        return '';
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