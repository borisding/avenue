<?php
namespace Avenue\Tests\Database;

use PDO;
use Avenue\App;
use Avenue\Database\Connection;
use Avenue\Tests\Reflection;

abstract class AbstractDatabaseTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    protected $connection;

    protected $config = [
        'appVersion' => '1.0',
        'httpVersion' => '1.1',
        'timezone' => 'UTC',
        'environment' => 'development',
        'database' => [
            'development' => [
                'master' => [
                    'dsn' => 'sqlite::memory:'
                ],
                'slave' => [
                    'dsn' => 'sqlite::memory:'
                ]
            ]
        ]
    ];

    public function setUp()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped("'pdo_sqlite' is required for testing sqlite memory.");
        }

        $this->app = new App();
        Reflection::setPropertyValue($this->app, 'config', $this->config, true);
        $this->connection = new Connection($this->app);
    }

    protected function createMemoryTable()
    {
        $tableSql = "CREATE TABLE programming_language (
            id   INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL
        )";

        $pdo = new PDO('sqlite::memory:');
        $pdo->exec($tableSql);
    }
}