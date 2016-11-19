<?php
namespace Avenue\Tests\Mocks;

class SessionHandler implements \SessionHandlerInterface
{
    public $data;

    protected $config = [
        'table' => 'session',
        'lifetime' => 0,
        'readSlave' => false,
        'encrypt' => false
    ];

    public function getAppSecret()
    {
        return 'fortestingonly';
    }

    public function getConfig($name = null)
    {
        if (empty($name)) {
            return $this->config;
        }

        return $this->config[$name];
    }

    protected function encrypt($value)
    {
        return $value;
    }

    protected function decrypt($value)
    {
        return $value;
    }

    public function open($savePath, $id)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        return isset($this->data[$id]) ? $this->data[$id] : '';
    }

    public function write($id, $value)
    {
        $this->data[$id] = $value;
        return true;
    }

    public function destroy($id)
    {
        $this->data[$id] = null;
        return true;
    }

    public function gc($lifetime)
    {
        return true;
    }
}
