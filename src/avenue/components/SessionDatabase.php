<?php
namespace Avenue\Components;

use Avenue\Database\Street;
use Avenue\Components\Encryption;
use SessionHandlerInterface;

class SessionDatabase extends Street implements SessionHandlerInterface
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
     * Default session configuration
     *
     * @var array
     */
    protected $config = [
        // table name
        'table' => 'session',
        // session lifetime
        'lifetime' => 0,
        // encrypt session's value
        'encrypt' => false
    ];
    
    /**
     * Weight of frequency to trigger garbage collection.
     *
     * @var integer
     */
    const GC_WEIGHT = 500;
    
    /**
     * Session database class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->config = array_merge($this->config, $this->app->getConfig('session'));
        $this->table = $this->config['table'];
        
        // get the encryption instance if 'encrypt' set as true
        if ($this->config['encrypt']) {
            $this->encryption = $this->app->encryption();
        }
    }
    
    /**
     * Invoked when session is being opened.
     * Occasionally trigger the garbage collection.
     * 
     * @see SessionHandlerInterface::open()
     */
    public function open($path, $name)
    {
        if (mt_rand(1, static::GC_WEIGHT) === static::GC_WEIGHT) {
            $this->gc($this->config['lifetime']);
        }
        
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
     * @see SessionHandlerInterface::write()
     */
    public function write($id, $value)
    {
        $this->value = $this->encrypt($value);
        $this->timestamp = time();
        
        return $this->upsert($id);
    }
    
    /**
     * Invoked once session is destroyed.
     * 
     * @see SessionHandlerInterface::destroy()
     */
    public function destroy($id)
    {
        return $this->remove($id);
    }
    
    /**
     * Garbage collection to clean up old data by removing it.
     * 
     * @see SessionHandlerInterface::gc()
     */
    public function gc($lifetime)
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
        if ($this->encryption instanceof Encryption) {
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
        if ($this->encryption instanceof Encryption) {
            return $this->encryption->get($data);
        }
        
        return base64_decode($data);
    }
}