<?php
namespace Avenue\Components;

use Avenue\Database\Street;
use Avenue\Components\Encryption;

class SessionDatabase extends Street
{
    /**
     * Session table name.
     *
     * @var string
     */
    protected $table;
    
    /**
     * Encryption class instance.
     *
     * @var mixed
     */
    protected $encryption;
    
    /**
     * Weight of frequency to trigger garbage collection.
     * 
     * @var integer
     */
    const GC_WEIGHT = 500;
    
    /**
     * Default session configuration
     *
     * @var array
     */
    protected $config = [
        // table name
        'table' => 'session',
        // session lifetime
        'lifetime' => 1200,
        // encrypt session's value
        'encrypt' => false
    ];
    
    /**
     * Session database class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->config = array_merge($this->config, $this->app->getConfig('session'));
        
        // get the encryption instance if 'encrypt' set as true
        if ($this->config['encrypt']) {
            $this->encryption = $this->app->encryption();
        }
        
        // get the table
        $this->table = $this->config['table'];
    }
    
    /**
     * Invoked when session is being opened.
     * Remove any expired session, ocassionally.
     */
    public function ssopen()
    {
        if (mt_rand(1, static::GC_WEIGHT) === static::GC_WEIGHT) {
            $this->ssgc($this->config['lifetime']);
        }
        
        return true;
    }
    
    /**
     * Invoked once session is written.
     *
     * @return boolean
     */
    public function ssclose()
    {
        session_write_close();
        return true;
    }
    
    /**
     * Retrieving the serialized session data inserted previously.
     *
     * @param mixed $id
     * @return mixed
     */
    public function ssread($id)
    {
        $result = $this
        ->find(['value'])
        ->where('id', $id)
        ->andWhere('timestamp', '>', $this->getExpired())
        ->getOne();
        
        if ($this->getTotalRows()) {
            return $this->decrypt($result['value']);
        }
        
        return '';
    }
    
    /**
     * Writing session values into table.
     *
     * @param mixed $id
     * @param mixed $value
     */
    public function sswrite($id, $value)
    {
        $this->value = $this->encrypt($value);
        $this->timestamp = time();
        
        return $this->upsert($id);
    }
    
    /**
     * Invoked once session is destroyed.
     * 
     * @param mixed $id
     */
    public function ssdestroy($id)
    {
        return $this->remove($id);
    }
    
    /**
     * Garbage collection to clean up old data by removing it.
     * 
     * @param mixed $lifetime
     */
    public function ssgc($lifetime)
    {
        $this
        ->removeWhere('timestamp', '<=', $this->getExpired($lifetime))
        ->run();
        
        return true;
    }
    
    /**
     * Get the expired time.
     * 
     * @param mixed $lifetime
     */
    protected function getExpired($lifetime = null)
    {
        if (empty($lifetime)) {
            $lifetime = $this->config['lifetime'];
        }
        
        return time() - intval($lifetime);
    }
    
    /**
     * Get the encrypted data.
     *
     * @param mixed $plaintext
     */
    protected function encrypt($plaintext)
    {
        if (is_object($this->encryption) && $this->encryption instanceof Encryption) {
            return $this->encryption->set($plaintext);
        }
        
        return base64_encode($plaintext);
    }
    
    /**
     * Decrypt the data.
     *
     * @param mixed $data
     */
    protected function decrypt($data)
    {
        if (is_object($this->encryption) && $this->encryption instanceof Encryption) {
            return $this->encryption->get($data);
        }
        
        return base64_decode($data);
    }
}