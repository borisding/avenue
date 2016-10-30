<?php
namespace Avenue\Tests\Database;

use Avenue\App;

abstract class AbstractDatabaseTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    protected $config = [
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
            $this->markTestSkipped('[pdo_sqlite] is required for testing sqlite memory.');
        }

        $this->app = new App($this->config);
    }
}