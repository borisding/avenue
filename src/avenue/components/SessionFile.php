<?php
namespace Avenue\Components;

use Avenue\App;
use Avenue\Components\Encryption;
use SessionHandlerInterface;

class SessionFile implements SessionHandlerInterface
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
     * The save path for session files.
     *
     * @var mixed
     */
    protected $path;
    
    /**
     * Default session configuration
     *
     * @var array
     */
    protected $config = [
        // the save path
        'path' => '',
        // session lifetime
        'lifetime' => 0,
        // encrypt session's value
        'encrypt' => false
    ];
    
    /**
     * Session file prefix.
     * 
     * @var string
     */
    const FILE_PREFIX = 'avenue_sess_';
    
    /**
     * Session file class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->config = array_merge($this->config, $this->app->getConfig('session'));
        $this->path = !empty($this->config['path']) ? $this->config['path'] : session_save_path();
        
        // get the encryption instance if 'encrypt' set as true
        if ($this->config['encrypt']) {
            $this->encryption = $this->app->encryption();
        }
    }
    
    /**
     * Invoked when session is being opened.
     * Create one if there is no directory and,
     * remove any expired session, ocassionally.
     * 
     * @see SessionHandlerInterface::open()
     */
    public function open($savePath, $sessionName)
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777);
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
        $file = $this->getSessionFile($id);
        
        if (!file_exists($file)) {
            return '';
        }
        
        $value = (string)file_get_contents($file);
        return $this->decrypt($value);
    }
    
    /**
     * Writing session values into file.
     * 
     * @see SessionHandlerInterface::write()
     */
    public function write($id, $value)
    {
        $value = $this->encrypt($value);
        $file = $this->getSessionFile($id);
        
        return file_put_contents($file, $value) !== false;
    }
    
    /**
     * Invoked once session is destroyed.
     * 
     * @see SessionHandlerInterface::destroy()
     */
    public function destroy($id)
    {
        $file = $this->getSessionFile($id);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    /**
     * Garbage collection to clean up old data by removing it.
     * 
     * @see SessionHandlerInterface::gc()
     */
    public function gc($lifetime)
    {
        $pattern = $this->path . '/' . static::FILE_PREFIX . '*';
        $expired = time() - intval($this->config['lifetime']);
        
        foreach (glob($pattern) as $file) {
            if (file_exists($file) && (filemtime($file) + intval($lifetime)) <= $expired) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    /**
     * Get the session file based on id.
     * 
     * @param mixed $id
     * @return string
     */
    protected function getSessionFile($id)
    {
        return $this->path . '/' . static::FILE_PREFIX . $id;
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