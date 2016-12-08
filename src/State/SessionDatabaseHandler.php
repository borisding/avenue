<?php
namespace Avenue\State;

use SessionHandlerInterface;
use Avenue\App;
use Avenue\Database\Command;
use Avenue\State\SessionHandler;

class SessionDatabaseHandler extends SessionHandler implements SessionHandlerInterface
{
    /**
     * Database class instance.
     *
     * @var \Avenue\Database\Command
     */
    protected $db;

    /**
     * Database session table.
     *
     * @var mixed
     */
    protected $table;

    /**
     * Reading from slave flag.
     *
     * @var boolean
     */
    protected $readSlave;

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
        parent::__construct($app, $config);

        $this->table = $this->getConfig('table');
        $this->readSlave = $this->getConfig('readSlave') === true;

        // instantiate db instance
        if (!$this->db instanceof Command) {
            $this->db = new Command();
        }
    }

    /**
     * Invoked when session is being opened.
     * Occasionally, trigger the garbage collection.
     *
     * @param  mixed $path
     * @param  mixed $name
     * @return boolean
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
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Retrieving the session data inserted previously.
     *
     * @param  mixed $id
     * @return mixed
     */
    public function read($id)
    {
        $sql = sprintf('select value from %s where id = :id', $this->table);
        $value = $this->db->cmd($sql, $this->readSlave)->bind(':id', $id)->column();

        if ($value) {
            return $this->decrypt($value);
        }

        return '';
    }

    /**
     * Writing session into table.
     *
     * @param  mixed $id
     * @param  mixed $value
     * @return boolean
     */
    public function write($id, $value)
    {
        $params = [
            ':id' => $id,
            ':value' => $this->encrypt($value),
            ':timestamp' => time()
        ];

        // mysql/maria
        if ($this->db->getConnectionInstance()->getMasterDriver() == 'mysql') {
            $sql = sprintf('replace into %s values (:id, :value, :timestamp)', $this->table);
        // others
        } else {
            $sql = sprintf('select count(id) as total from %s where id = :id', $this->table);
            $total = $this->db->cmd($sql)->bind(':id', $id)->column();

            if ($total > 0) {
                $sql = sprintf('update %s set value = :value, timestamp = :timestamp where id = :id', $this->table);
            } else {
                $sql = sprintf('insert into %s values (:id, :value, :timestamp) ', $this->table);
            }
        }

        $this->db->cmd($sql)->runWith($params);
        return true;
    }

    /**
     * Invoked once session is destroyed.
     *
     * @param  mixed $id
     * @return boolean
     */
    public function destroy($id)
    {
        $sql = sprintf('delete from %s where id = :id', $this->table);
        $this->db->cmd($sql)->runWith([':id' => $id]);

        return true;
    }

    /**
     * Garbage collection to clean up old data by removing it.
     *
     * @param  integer $lifetime
     * @return boolean
     */
    public function gc($lifetime)
    {
        $previous = time() - intval($lifetime);
        $sql = sprintf('delete from %s where timestamp < :timestamp', $this->table);

        $this->db->cmd($sql)->runWith([':timestamp' => $previous]);
        return true;
    }
}
