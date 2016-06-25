<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected $db = [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'base_repo_test_db',
        'username'  => 'homestead',
        'password'  => 'secret',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->setupDatabase();

        $capsule = new Capsule;

        $capsule->addConnection($this->db);

        // Set the event dispatcher used by Eloquent models... (optional)        
        $capsule->setEventDispatcher(new Dispatcher(new Container));

        // Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $capsule->bootEloquent();
    }

    public function setupDatabase()
    {
        $this->pdoExec('create database if not exists '.$this->db['database']);
    }

    public function pdoExec($sql)
    {
        $pdo = new PDO('mysql:host=localhost', 'homestead', 'secret');
        $pdo->exec($sql);
    }
}
