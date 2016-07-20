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

    public function seed()
    {
        $spies = [
            [
                'username' => 'markii1607',
                'password' => 'secret',
                'name' => 'mark',
                'xp' => 172
            ], [
                'username' => 'katbritanico',
                'password' => 'secret',
                'name' => 'katrina',
                'xp' => 57
            ], [
                'username' => 'janelagatuz',
                'password' => 'secret',
                'name' => 'janelle',
                'xp' => 352
            ],
        ];
        foreach ($spies as $spy) {
            $this->repo->create($spy);
        }
    }

    public function testShouldFetchWithGivenAttributes()
    {
        $this->seed();

        $attributes = [
            'username',
            'name'
        ];

        $fetchSpies = $this->repo->fetch($attributes);

        $this->assertTrue(count($fetchSpies) > 0, 'No fetched record of spies.');

        foreach ($fetchSpies as $fetchSpy) {
            $this->assertEquals(array_keys($fetchSpy->getAttributes()), $attributes);
        }
    }

    public function testShouldFetchWithAppliedFilters()
    {
        // $filters = [
        //     'xp' => [
        //         '=' => [352],
        //         '>' => [12],
        //         '!=' => [1, 2, 3, 4, 5, 6, 8, 10, 12, 13]
        //     ]
        // ];
        
        $this->seed();

        // Test `=`
        $spies = $this->repo->fetch(null, ['xp' => [
            '=' => [352]
        ]]);

        $this->assertEquals(1, $spies->count());

        // Test `!=`
        $spies = $this->repo->fetch(null, ['xp' => [
            '!=' => [352]
        ]]);

        $this->assertEquals(2, $spies->count());

        // Test `>`
        $spies = $this->repo->fetch(null, ['xp' => [
            '>' => [100]
        ]]);

        $this->assertEquals(2, $spies->count());

        // Test `<`
        $spies = $this->repo->fetch(null, ['xp' => [
            '<' => [100]
        ]]);

        $this->assertEquals(1, $spies->count());

        // TODO: Create test for >= and <=
    }
}
