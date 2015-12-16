<?php
namespace Avenue\Components;

use Avenue\Database\PdoAdapter;
use Avenue\Components\Encryption;

class SessionDatabase extends PdoAdapter
{
    /**
     * Session table name.
     *
     * @var string
     */
    protected $table = 'session';
    
    /**
     * Primary key for session table.
     *
     * @var string
     */
    protected $pk = 'id';
    
    /**
     * Session data.
     *
     * @var mixed
     */
    protected $ssdata;
    
    /**
     * Flag for session state.
     *
     * @var boolean
     */
    protected $state;
    
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
    }
    
    /**
     * Invoked when session is being opened.
     * Remove any expired session, if any.
     */
    public function ssopen()
    {
        $lifetime = (int) $this->config['lifetime'];
        return $this->ssgc($lifetime);
    }
    
    /**
     * Invoked once session is written.
     *
     * @return boolean
     */
    public function ssclose()
    {
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
        ->cmd(sprintf('SELECT data FROM %s WHERE id = :id', $this->table))
        ->bind(':id', $id)
        ->fetchOne();
        
        if ($this->getTotalRows()) {
            $this->ssdata = $this->decrypt($result['data']);
            $this->state = true;
        } else {
            $this->ssdata = '';
            $this->state = false;
        }
        
        return $this->ssdata;
    }
    
    /**
     * Writing session values into table.
     *
     * @param mixed $id
     * @param mixed $data
     */
    public function sswrite($id, $data)
    {
        $data = $this->encrypt($data);
        $this->ssdata = [':id' => $id, ':data' => $data, ':timestamp' => time()];
        
        if ($this->state) {
            $sql = sprintf('UPDATE %s SET data = :data, timestamp = :timestamp WHERE id = :id', $this->table);
        } else {
            $sql = sprintf('INSERT INTO %s VALUES (:id, :data, :timestamp)', $this->table);
        }
        
        $query = $this
        ->cmd($sql)
        ->batch($this->ssdata)
        ->run();
        
        return $this->state = true;
    }
    
    /**
     * Invoked once session is destroyed.
     * 
     * @param mixed $id
     */
    public function ssdestroy($id)
    {
        $query = $this
        ->cmd(sprintf('DELETE FROM %s WHERE id = :id', $this->table))
        ->bind(':id', $id);
        
        return $query->run();
    }
    
    /**
     * Garbage collection to clean up old data by removing it.
     * 
     * @param mixed $lifetime
     */
    public function ssgc($lifetime)
    {
        $expired = time() - $lifetime;
        $query = $this
        ->cmd(sprintf('DELETE FROM %s WHERE timestamp <= :expired', $this->table))
        ->bind(':expired', $expired);
        
        return $query->run();
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
        } else {
            return base64_encode($plaintext);
        }
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
        } else {
            return base64_decode($data);
        }
    }
}