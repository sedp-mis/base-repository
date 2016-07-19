<?php

class BaseRepositoryEloquentTest extends TestCase
{
    protected $repo;

    public function setUp()
    {
        parent::setUp();

        $table = "`{$this->db['database']}`.`spies`";

        $this->pdoExec("DROP TABLE IF EXISTS {$table}");
        
        $this->pdoExec("
            CREATE TABLE IF NOT EXISTS {$table} (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `username` varchar(255) NULL COMMENT '',
              `password` varchar(255) NULL COMMENT '',
              `name` varchar(255) NOT NULL COMMENT '',
              `xp` int(10) unsigned DEFAULT NULL COMMENT '',
              `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");

        $this->repo = new SpyRepositoryEloquent(new Spy);
    }

    public function testShouldCreateStoreFindAndUpdateModel()
    {
        $spy = $this->repo->create([
            'username' => 'ajcastro',
            'password' => 'password',
            'name' => 'arjon',
            'xp' => 99
        ]);

        $storedSpy = $this->repo->find($spy->id);

        $this->assertTrue($spy instanceof Spy);
        $this->assertTrue($storedSpy instanceof Spy);
        $this->assertEquals($storedSpy->getAttributes(), $spy->getAttributes());

        $storedSpy->name = 'ajcastro';

        $this->repo->update(['name' => $storedSpy->name], $storedSpy->id);

        $updatedSpy = $this->repo->find($storedSpy->id);
        $this->assertEquals($updatedSpy->getAttributes(), $storedSpy->getAttributes());
    }

    public function testShouldFetchWithGivenAttributes()
    {
        $spies = [
            [
                'username' => 'markii1607',
                'password' => 'secret',
                'name' => 'mark',
                'xp' => 99
            ],
            [
                'username' => 'katbritanico',
                'password' => 'secret',
                'name' => 'katrina',
                'xp' => 99
            ],
            [
                'username' => 'janelagatuz',
                'password' => 'secret',
                'name' => 'janelle',
                'xp' => 99
            ],
            [
                'username' => 'jkmendez',
                'password' => 'secret',
                'name' => 'ken',
                'xp' => 99
            ],
            [
                'username' => 'guyabani',
                'password' => 'secret',
                'name' => 'giovani',
                'xp' => 99
            ],
            [
                'username' => 'ja9',
                'password' => 'secret',
                'name' => 'janine',
                'xp' => 99
            ],
            [
                'username' => 'aceruser22',
                'password' => 'secret',
                'name' => 'ace',
                'xp' => 99
            ],
            [
                'username' => 'jmoane',
                'password' => 'secret',
                'name' => 'jessa',
                'xp' => 99
            ],
            [
                'username' => 'cklucido',
                'password' => 'secret',
                'name' => 'kaye',
                'xp' => 99
            ],
            [
                'username' => 'jpintor',
                'password' => 'secret',
                'name' => 'jazarr',
                'xp' => 99
            ],
            [
                'username' => 'kbagasbas',
                'password' => 'secret',
                'name' => 'karen',
                'xp' => 99
            ],
            [
                'username' => 'tinejoy',
                'password' => 'secret',
                'name' => 'tine',
                'xp' => 99
            ],
            [
                'username' => 'rodabby',
                'password' => 'secret',
                'name' => 'abby',
                'xp' => 99
            ]
        ];

        $attributes = [
            'username',
            'name'
        ];

        foreach ($spies as $spy)
        {
            $resultSpy = $this->repo->create($spy);
        }

        $fetchSpies = $this->repo->fetch($attributes, [], [], null, 0);

        foreach ($fetchSpies as $fetchSpy) {
            $this->assertEquals(array_keys($fetchSpy->getAttributes()), $attributes);
        }
        // dd($this->repo->fetch()->toArray());
    }
}
