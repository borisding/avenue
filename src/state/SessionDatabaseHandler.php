<?php
namespace Avenue\State;

use Avenue\App;
use Avenue\Mcrypt;
use Avenue\Database\Command;
use SessionHandlerInterface;

class SessionDatabaseHandler extends Command implements SessionHandlerInterface
{
    /**
     * Mcrypt class instance.
     *
     * @var mixed
     */
    private $mcrypt;

    /**
     * Default session configuration
     *
     * @var array
     */
    private $config = [
        'table' => 'session',
        'lifetime' => 0,
        'encrypt' => false,
        'readMaster' => true,
        'secret' => ''
    ];

    /**
     * Reading from master flag.
     *
     * @var boolean
     */
    private $readMasterBool;

    /**
     * Weight of frequency to trigger garbage collection.
     *
     * @var integer
     */
    const GC_WEIGHT = 500;

    /**
     * Session database class constructor.
     *
     * @param App $app
     * @param array $config
     */
    public function __construct(App $app, array $config = [])
    {
        parent::__construct($app);

        $this->config = array_merge($this->config, $config);
        $this->table = $this->getSessionConfig('table');

        // set read master flag
        $this->readMasterBool = $this->getSessionConfig('readMaster') === true;

        // get the mcrypt instance if 'encrypt' set as true
        if ($this->getSessionConfig('encrypt')) {
            $this->mcrypt = $this->app->mcrypt();
        }

        // set gc max lifetime at run time
        ini_set('session.gc_maxlifetime', intval($this->getSessionConfig('lifetime')));

        // set session cookie accessible via http only
        ini_set('session.cookie_httponly', 1);
    }

    /**
     * Invoked when session is being opened.
     * Occasionally trigger the garbage collection.
     *
     * {@inheritDoc}
     * @see SessionHandlerInterface::open()
     */
    public function open($path, $name)
    {
        if (mt_rand(1, static::GC_WEIGHT) === static::GC_WEIGHT) {
            $this->gc($this->getSessionConfig('lifetime'));
        }

        return true;
    }

    /**
     * Invoked once session is written.
     *
     * {@inheritDoc}
     * @see SessionHandlerInterface::close()
     */
    public function close()
    {
        return true;
    }

    /**
     * Retrieving the session data inserted previously.
     *
     * {@inheritDoc}
     * @see SessionHandlerInterface::read()
     */
    public function read($id)
    {
        $sql = sprintf('select value from %s where id = :id', $this->table);
        $value = $this->cmd($sql, $this->readMasterBool)->bind(':id', $id)->fetchColumn();

        if ($value) {
            return $this->decrypt($value);
        }

        return null;
    }

    /**
     * Writing session into table.
     *
     * {@inheritDoc}
     * @see SessionHandlerInterface::write()
     */
    public function write($id, $value)
    {
        $params = [
            ':id' => $id,
            ':value' => $this->encrypt($value),
            ':timestamp' => time()
        ];

        // mysql/maria
        if ($this->getMasterDriver() == 'mysql') {
            $sql = sprintf('replace into %s values (:id, :value, :timestamp)', $this->table);
        // others
        } else {
            $sql = sprintf('select count(id) as total from %s where id = :id', $this->table);
            $total = $this->cmd($sql)->bind(':id', $id)->fetchColumn();

            if ($total > 0) {
                $sql = sprintf('update %s set value = :value, timestamp = :timestamp where id = :id', $this->table);
            } else {
                $sql = sprintf('insert into %s values (:id, :value, :timestamp) ', $this->table);
            }
        }

        return $this->cmd($sql)->runWith($params);
    }

    /**
     * Invoked once session is destroyed.
     *
     * @see SessionHandlerInterface::destroy()
     */
    public function destroy($id)
    {
        $sql = sprintf('delete from %s where id = :id', $this->table);
        return $this->cmd($sql)->runWith([':id' => $id]);
    }

    /**
     * Garbage collection to clean up old data by removing it.
     *
     * @see SessionHandlerInterface::gc()
     */
    public function gc($lifetime)
    {
        $previous = time() - intval($lifetime);
        $sql = sprintf('delete from %s where timestamp < :timestamp', $this->table);

        return $this->cmd($sql)->runWith([':timestamp' => $previous]);
    }

    /**
     * Return the session database config based on the name.
     *
     * @param mixed $name
     */
    public function getSessionConfig($name)
    {
        return $this->app->arrGet($name, $this->config);
    }

    /**
     * Get the encrypted session value.
     *
     * @param mixed $value
     */
    private function encrypt($value)
    {
        if ($this->mcrypt instanceof Mcrypt) {
            return $this->mcrypt->encrypt($value);
        }

        return $value;
    }

    /**
     * Decrypt the session value.
     *
     * @param mixed $value
     */
    private function decrypt($value)
    {
        if ($this->mcrypt instanceof Mcrypt) {
            return $this->mcrypt->decrypt($value);
        }

        return $value;
    }
}