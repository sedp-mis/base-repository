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
                'xp' => 172
            ],
            [
                'username' => 'katbritanico',
                'password' => 'secret',
                'name' => 'katrina',
                'xp' => 57
            ],
            [
                'username' => 'janelagatuz',
                'password' => 'secret',
                'name' => 'janelle',
                'xp' => 352
            ],
            [
                'username' => 'jkmendez',
                'password' => 'secret',
                'name' => 'ken',
                'xp' => 100
            ],
            [
                'username' => 'guyabani',
                'password' => 'secret',
                'name' => 'giovani',
                'xp' => 67
            ],
            [
                'username' => 'ja9',
                'password' => 'secret',
                'name' => 'janine',
                'xp' => 10
            ],
            [
                'username' => 'aceruser22',
                'password' => 'secret',
                'name' => 'ace',
                'xp' => 52
            ],
            [
                'username' => 'jmoane',
                'password' => 'secret',
                'name' => 'jessa',
                'xp' => 281
            ],
            [
                'username' => 'cklucido',
                'password' => 'secret',
                'name' => 'kaye',
                'xp' => 72
            ],
            [
                'username' => 'jpintor',
                'password' => 'secret',
                'name' => 'jazarr',
                'xp' => 562
            ],
            [
                'username' => 'kbagasbas',
                'password' => 'secret',
                'name' => 'karen',
                'xp' => 527
            ],
            [
                'username' => 'tinejoy',
                'password' => 'secret',
                'name' => 'tine',
                'xp' => 732
            ],
            [
                'username' => 'rodabby',
                'password' => 'secret',
                'name' => 'abby',
                'xp' => 162
            ]
        ];

        $attributes = [
            'username',
            'name'
        ];

        $filters = [
            'id' => [
                '=' => [1, 2, 3],
                '>' => [12],
                '!=' => [1, 2, 3, 4, 5, 6, 8, 10, 12, 13]
            ]
        ];

        foreach ($spies as $spy)
        {
            $resultSpy = $this->repo->create($spy);
        }

        $fetchSpies = $this->repo->fetch($attributes, $filters, null, null, 0);

        // foreach ($fetchSpies as $fetchSpy) {
        //     $this->assertEquals(array_keys($fetchSpy->getAttributes()), $attributes);
        // }
        dd($fetchSpies);
        
    }
}
