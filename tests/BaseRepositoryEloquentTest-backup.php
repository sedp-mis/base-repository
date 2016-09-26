<?php

// Sample URL
// http://localhost:8000/api/v1/posts?relations[]=label&relations[comments][attributes][]=id&relations[comments][attributes][]=text

class BaseRepositoryEloquentTest /* extends TestCase*/
{
    protected $repo;

    public function setUp()
    {
        parent::setUp();

        Artisan::call('migrate', ['--force']);

        $this->repo = new SpyRepositoryEloquent(new Spy);
    }

    public function testShouldCreateStoreFindAndUpdateModel()
    {
        $spy = $this->repo->create([
            'username' => 'ajcastro',
            'password' => 'password',
            'name'     => 'arjon',
            'xp'       => 99,
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

    public function seedDb()
    {
        $spies = [
            [
                'username' => 'markii1607',
                'password' => 'secret',
                'name'     => 'mark',
                'xp'       => 172,
            ], [
                'username' => 'katbritanico',
                'password' => 'secret',
                'name'     => 'katrina',
                'xp'       => 57,
            ], [
                'username' => 'janelagatuz',
                'password' => 'secret',
                'name'     => 'janelle',
                'xp'       => 352,
            ],
        ];
        foreach ($spies as $spy) {
            $this->repo->create($spy);
        }
    }

    public function seedMoreForPaginate()
    {
        $spies = [
            [
                'username' => 'guyabani',
                'password' => 'secret',
                'name'     => 'giovani',
                'xp'       => 512,
            ], [
                'username' => 'kenn ken',
                'password' => 'secret',
                'name'     => 'ken',
                'xp'       => 86,
            ], [
                'username' => 'ja9',
                'password' => 'secret',
                'name'     => 'janine',
                'xp'       => 182,
            ], [
                'username' => 'aceruser19',
                'password' => 'secret',
                'name'     => 'ace',
                'xp'       => 281,
            ], [
                'username' => 'jmoane',
                'password' => 'secret',
                'name'     => 'jessa',
                'xp'       => 41,
            ], [
                'username' => 'cklucido',
                'password' => 'secret',
                'name'     => 'kaye',
                'xp'       => 145,
            ], [
                'username' => 'joypintor',
                'password' => 'secret',
                'name'     => 'jazarr',
                'xp'       => 621,
            ], [
                'username' => 'kharen',
                'password' => 'secret',
                'name'     => 'karen',
                'xp'       => 91,
            ], [
                'username' => 'tinejoy',
                'password' => 'secret',
                'name'     => 'tine',
                'xp'       => 321,
            ],
        ];
        foreach ($spies as $spy) {
            $this->repo->create($spy);
        }
    }

    public function testShouldFetchWithGivenAttributes()
    {
        $this->seedDb();

        $attributes = [
            'username',
            'xp',
        ];

        $fetchSpies = $this->repo->fetch($attributes);

        $this->assertTrue(count($fetchSpies) > 0, 'No fetched record of spies.');

        foreach ($fetchSpies as $fetchSpy) {
            $this->assertEquals(array_keys($fetchSpy->getAttributes()), $attributes);
        }
    }

    public function testFetchFiltersUsingEquals()
    {
        $this->seedDb();

        // Test `=` with single value
        $spies = $this->repo->fetch(null, ['xp' => [
            '=' => 352,
        ]]);

        $this->assertEquals(1, $spies->count());

        // Test `=` with array of values
        $spies = $this->repo->fetch(null, ['xp' => [
            '=' => [352],
        ]]);

        $this->assertEquals(1, $spies->count());

        $spies = $this->repo->fetch(null, ['xp' => [
            '=' => [352, 57],
        ]]);

        $this->assertEquals(2, $spies->count());

        $spies = $this->repo->fetch(null, ['xp' => [
            '=' => [352, 57, 172],
        ]]);

        $this->assertEquals(3, $spies->count());
    }

    public function testFetchFiltersUsingNotEquals()
    {
        $this->seedDb();

        // Test `!=` with single value
        $spies = $this->repo->fetch(null, ['xp' => [
            '!=' => 352,
        ]]);

        $this->assertEquals(2, $spies->count());

        // Test `!=` with array of values
        $spies = $this->repo->fetch(null, ['xp' => [
            '!=' => [352],
        ]]);

        $this->assertEquals(2, $spies->count());

        $spies = $this->repo->fetch(null, ['xp' => [
            '!=' => [352, 57],
        ]]);

        $this->assertEquals(1, $spies->count());

        $spies = $this->repo->fetch(null, ['xp' => [
            '!=' => [352, 57, 172],
        ]]);

        $this->assertEquals(0, $spies->count());
    }

    public function testFetchFiltersUsingGreaterThan()
    {
        $this->seedDb();

        // Test `>`
        $spies = $this->repo->fetch(null, ['xp' => [
            '>' => [100],
        ]]);

        $this->assertEquals(2, $spies->count());
    }

    public function testFetchFiltersUsingLessThan()
    {
        $this->seedDb();

        // Test `<`
        $spies = $this->repo->fetch(null, ['xp' => [
            '<' => [100],
        ]]);

        $this->assertEquals(1, $spies->count());
    }

    public function testFetchFiltersUsingGreaterThanOrEqual()
    {
        $this->seedDb();

        // Test `>=`
        $spies = $this->repo->fetch(null, ['xp' => [
            '>=' => [180],
        ]]);

        $this->assertEquals(1, $spies->count());
    }

    public function testFetchFiltersUsingLessThanOrEqual()
    {
        $this->seedDb();

        // Test `<=`
        $spies = $this->repo->fetch(null, ['xp' => [
            '<=' => [172],
        ]]);

        $this->assertEquals(2, $spies->count());
    }

    public function testFetchSortAscAndDesc()
    {
        $this->seedDb();

        // Test for name in ascending order
        $spies = $this->repo->fetch(null, null, [
            'name' => 'asc',
        ]);

        $this->assertEquals($spies->first()->name, 'janelle');
        $this->assertEquals($spies->last()->name, 'mark');

        // Test for xp in descending order
        $spies = $this->repo->fetch(null, null, [
            'xp' => 'desc',
        ]);

        $this->assertEquals($spies->first()->xp, 352);
        $this->assertEquals($spies->last()->xp, 57);
    }

    public function seedForTestingMultipleSort()
    {
        $spies = [
            [
                'id'   => 1,
                'name' => 'anna',
                'xp'   => 2,
            ], [
                'id'   => 2,
                'name' => 'anna',
                'xp'   => 3,
            ], [
                'id'   => 3,
                'name' => 'anna',
                'xp'   => 4,
            ],
        ];

        foreach ($spies as $spy) {
            $this->repo->create($spy);
        }
    }

    public function testFetchShouldSortUsingMultiple()
    {
        $this->seedForTestingMultipleSort();

        $spies = $this->repo->fetch(null, null, [
            'name' => 'asc',
            'xp'   => 'desc',
        ]);

        $this->assertEquals($spies->first()->id, 3);
        $this->assertEquals($spies->last()->id, 1);
    }

    public function testFetchLimitAndSkip()
    {
        $this->seedDb();

        // Test with limit of 2
        $spies = $this->repo->fetch(null, null, null, 2);

        $this->assertTrue(count($spies) == 2, 'Spies collected: '.count($spies));

        // Test with limit 1
        $spies = $this->repo->fetch(null, null, null, 1);

        $this->assertTrue(count($spies) == 1, 'Spies collected: '.count($spies));

        // Test with limit 2 offset 1
        $spies = $this->repo->fetch(null, null, null, 2, 1);

        $this->assertTrue(count($spies) == 2, 'Spies collected: '.count($spies));
        $this->assertEquals($spies->first()->id, 2);

        // Test with limit 1 offset 2
        $spies = $this->repo->fetch(null, null, null, 1, 2);

        $this->assertTrue(count($spies) == 1, 'Spies collected: '.count($spies));
        $this->assertEquals($spies->first()->id, 3);
    }

    public function testFetchForAllParameters()
    {
        $this->seedDb();

        $attributes = [
            'username',
            'name',
        ];

        $filters = [
            'xp' => [
                '=' => [
                    352,
                    57,
                ],
            ],
        ];

        $sort = [
            'name' => 'desc',
        ];

        $spies = $this->repo->fetch($attributes, $filters, $sort, 1, 1);

        $this->assertTrue(count($spies) == 1, 'Spies Count: '.count($spies));

        foreach ($spies as $spy) {
            $this->assertEquals(array_keys($spy->getAttributes()), $attributes);
        }

        $this->assertEquals($spies->first()->name, 'janelle');
    }

    public function testPagination()
    {
        $this->seedDb();
        $this->seedMoreForPaginate();

        $attributes = [
            'username',
            'name',
        ];

        $filters = [
            'xp' => [
                '=' => [
                    352,
                    57,
                    86,
                    145,
                    41,
                    321,
                    512,
                    91,
                ],
            ],
        ];

        $sort = [
            'name' => 'desc',
        ];

        // page 1, 3 data per page
        $spies = $this->repo->paginate($attributes, $filters, $sort, 3, 1);

        $this->assertTrue(count($spies) == 3, 'Spies collected: '.count($spies));
        $this->assertEquals($spies[0]->getAttributes()['name'], 'tine');
        $this->assertEquals($spies[count($spies) - 1]->getAttributes()['name'], 'kaye');

        // page 3, 3 data per page
        $spies = $this->repo->paginate($attributes, $filters, $sort, 3, 3);

        $this->assertTrue(count($spies) == 2, 'Spies collected: '.count($spies));
        $this->assertEquals($spies[0]->getAttributes()['name'], 'janelle');
        $this->assertEquals($spies[count($spies) - 1]->getAttributes()['name'], 'giovani');

        // page 2, 4 data per page
        $spies = $this->repo->paginate($attributes, $filters, $sort, 4, 2);

        $this->assertTrue(count($spies) == 4, 'Spies collected: '.count($spies));
        $this->assertEquals($spies[0]->getAttributes()['name'], 'karen');
        $this->assertEquals($spies[count($spies) - 1]->getAttributes()['name'], 'giovani');

        // page 2, 2 data per page
        $spies = $this->repo->paginate($attributes, $filters, $sort, 2, 2);

        $this->assertTrue(count($spies) == 2, 'Spies collected: '.count($spies));
        $this->assertEquals($spies[0]->getAttributes()['name'], 'kaye');
        $this->assertEquals($spies[count($spies) - 1]->getAttributes()['name'], 'katrina');
    }

    public function seedWithTarget()
    {
        $this->seedDb();

        $spy = Spy::first();

        $spy->target()->save(new Target(['name' => 'laptop']));
    }

    public function testShouldEagerLoadRelations()
    {
        $this->seedWithTarget();

        $spy = $this->repo->with('target')->first();

        $this->assertTrue($spy->getRelation('target') instanceof Target);
    }

    public function testShouldEagerLoadRelationsWithAttributes()
    {
        $this->seedWithTarget();

        $relations = [
            'target' => [
                'attributes' => [
                    'id',
                    'name',
                ],
            ],
        ];

        $spy = $this->repo->with($relations)->first();

        $this->assertTrue($spy->getRelation('target') instanceof Target);
    }

    public function testSearch()
    {
        $this->seedDb();
        $spies = $this->repo->search('kat');

        $this->assertEquals(1, $spies->count());
        $this->assertEquals('katbritanico', $spies->first()->username);

        $spies = $this->repo->search('mark');

        $this->assertEquals(1, $spies->count());
        $this->assertEquals('markii1607', $spies->first()->username);
    }
}
