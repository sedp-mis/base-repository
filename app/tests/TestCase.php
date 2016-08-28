<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
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

	/**
	 * Creates the application.
	 *
	 * @return \Symfony\Component\HttpKernel\HttpKernelInterface
	 */
	public function createApplication()
	{
		$unitTesting = true;

		$testEnvironment = 'testing';

		return require __DIR__.'/../../bootstrap/start.php';
	}

	public function setUp()
    {
        parent::setUp();

        $this->setupDatabase();
    }

    public function setupDatabase()
    {
        $this->pdoExec('create database if not exists '.$this->db['database']);
    }

    public function pdoExec($sql)
    {
        $driver   = $this->db['driver'];
        $host     = $this->db['host'];
        $username = $this->db['username'];
        $password = $this->db['password'];
        $pdo = new PDO("{$driver}:host={$host}", "{$username}", "{$password}");
        $pdo->exec($sql);
    }
}
