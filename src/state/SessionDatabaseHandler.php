<?php
namespace Avenue\State;

use Avenue\App;
use Avenue\Mcrypt;
use Avenue\Database\Command;
use SessionHandlerInterface;

class SessionDatabaseHandler implements SessionHandlerInterface
{
    /**
     * App class instance.
     *
     * @var mixed
     */
    protected $app;

    /**
     * Database class instance.
     *
     * @var mixed
     */
    protected $db;

    /**
     * Mcrypt class instance.
     *
     * @var mixed
     */
    protected $mcrypt;

    /**
     * Default session configuration
     *
     * @var array
     */
    protected $config = [
        'table' => 'session',
        'lifetime' => 0,
        'encrypt' => false,
        'readMaster' => true,
        'secret' => ''
    ];

    /**
     * Database session table.
     *
     * @var mixed
     */
    protected $table;

    /**
     * Reading from master flag.
     *
     * @var boolean
     */
    protected $readMaster;

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
        $this->app = $app;
        $this->config = array_merge($this->config, $config);

        $this->table = $this->getConfig('table');
        $this->readMaster = $this->getConfig('readMaster') === true;

        // instantiate db instance
        if (!$this->db instanceof Command) {
            $this->db = new Command($app);
        }

        // get the mcrypt instance if 'encrypt' set as true
        if ($this->getConfig('encrypt')) {
            $this->mcrypt = $this->app->mcrypt();
        }
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
            $this->gc($this->getConfig('lifetime'));
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
        $value = $this->db->cmd($sql, $this->readMaster)->bind(':id', $id)->fetchColumn();

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
        if ($this->db->getMasterDriver() == 'mysql') {
            $sql = sprintf('replace into %s values (:id, :value, :timestamp)', $this->table);
        // others
        } else {
            $sql = sprintf('select count(id) as total from %s where id = :id', $this->table);
            $total = $this->db->cmd($sql)->bind(':id', $id)->fetchColumn();

            if ($total > 0) {
                $sql = sprintf('update %s set value = :value, timestamp = :timestamp where id = :id', $this->table);
            } else {
                $sql = sprintf('insert into %s values (:id, :value, :timestamp) ', $this->table);
            }
        }

        return $this->db->cmd($sql)->runWith($params);
    }

    /**
     * Invoked once session is destroyed.
     *
     * @see SessionHandlerInterface::destroy()
     */
    public function destroy($id)
    {
        $sql = sprintf('delete from %s where id = :id', $this->table);
        return $this->db->cmd($sql)->runWith([':id' => $id]);
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

        return $this->db->cmd($sql)->runWith([':timestamp' => $previous]);
    }

    /**
     * Return the session database config based on the name.
     *
     * @param mixed $name
     */
    public function getConfig($name)
    {
        return $this->app->arrGet($name, $this->config);
    }

    /**
     * Get the encrypted session value.
     *
     * @param mixed $value
     */
    protected function encrypt($value)
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
    protected function decrypt($value)
    {
        if ($this->mcrypt instanceof Mcrypt) {
            return $this->mcrypt->decrypt($value);
        }

        return $value;
    }
}