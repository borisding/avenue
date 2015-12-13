<?php
namespace Avenue\Components;

use Avenue\App;

class Encryption
{
    /**
     * App class instance.
     * 
     * @var mixed
     */
    protected $app;
    
    /**
     * The hashed key for encryption.
     *
     * @var mixed
     */
    protected $key;
    
    /**
     * Cipher algorithm.
     *
     * @var mixed
     */
    protected $cipher;
    
    /**
     * Mode for encryption.
     *
     * @var mixed
     */
    protected $mode;
    
    /**
     * Encryption class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $config = $this->app->getConfig('encryption');
        $this->key = $this->app->arrGet('key', $config);
        $this->cipher = $this->app->arrGet('cipher', $config, MCRYPT_RIJNDAEL_256);
        $this->mode = $this->app->arrGet('mode', $config, MCRYPT_MODE_CBC);
    }
    
    /**
     * Set method for encrypting plain data.
     * 
     * @param mixed $plaintext
     * @return string
     */
    public function set($plaintext)
    {
        $key = $this->generateKey();
        $cipher = $this->cipher;
        $mode = $this->mode;
        
        $ivSize = mcrypt_get_iv_size($cipher, $mode);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_RANDOM);
        $encrypted = $iv.mcrypt_encrypt($cipher, $key, $plaintext, $mode, $iv);
        
        return base64_encode($encrypted);
    }
    
    /**
     * Get method for decrypting encrypted data.
     * 
     * @param mixed $encrypted
     * @return string
     */
    public function get($encrypted)
    {
        try {
            $data = base64_decode($encrypted, true);
            $key = $this->generateKey();
            $cipher = $this->cipher;
            $mode = $this->mode;
            
            $ivSize = mcrypt_get_iv_size($cipher, $mode);
            $iv = substr($data, 0, $ivSize);
            $data = substr($data, $ivSize);
            
            return mcrypt_decrypt($cipher, $key, $data, $mode, $iv);
        } catch (\Exception $e) {
            return '';
        }
    }
    
    /**
     * Get the list of supported cipher algorithms.
     * 
     * @param string $libDir
     */
    public function getSupportedCiphers($libDir = 'mcrypt.algorithms_dir')
    {
        return mcrypt_list_algorithms($libDir);
    }
    
    /**
     * Get the list of supported modes.
     * 
     * @param string $libDir
     */
    public function getSupportedModes($libDir = 'mcrypt.modes_dir')
    {
        return mcrypt_list_modes($libDir);
    }
    
    /**
     * Generate hashed key.
     * 
     * @throws \InvalidArgumentException
     */
    protected function generateKey()
    {
        if (empty($this->key)) {
            throw new \InvalidArgumentException('Key cannot be empty!');
        }
        
        $keySize = mcrypt_get_key_size($this->cipher, $this->mode);
        $hashedKey = hash('sha512', $this->key);
        
        return substr($hashedKey, 0, $keySize);
    }
}