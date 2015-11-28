<?php
namespace Avenue\Database;

use Avenue\Database\Connection;
use Avenue\Database\PdoAdapterInterface;

class PdoAdapter extends Connection implements PdoAdapterInterface
{
    /**
     * PdoAdapter class constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}