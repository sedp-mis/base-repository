<?php

// Sample URL
// http://localhost:8000/api/v1/posts?relations[]=label&relations[comments][attributes][]=id&relations[comments][attributes][]=text

class BaseRepositoryEloquentTest extends TestCase
{
    protected $repo;

    public function setUp()
    {
        parent::setUp();

        Artisan::call('migrate');

        DB::beginTransaction();

        $this->repo = new SpyRepositoryEloquent(new Spy);
    }

    public function tearDown()
    {
        parent::tearDown();

        DB::rollback();
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

    public function seedDb()
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

    public function seedMoreForPaginate()
    {
        $spies = [
            [
                'username' => 'guyabani',
                'password' => 'secret',
                'name' => 'giovani',
                'xp' => 512
            ], [
                'username' => 'kenn ken',
                'password' => 'secret',
                'name' => 'ken',
                'xp' => 86
            ], [
                'username' => 'ja9',
                'password' => 'secret',
                'name' => 'janine',
                'xp' => 182
            ], [
                'username' => 'aceruser19',
                'password' => 'secret',
                'name' => 'ace',
                'xp' => 281
            ], [
                'username' => 'jmoane',
                'password' => 'secret',
                'name' => 'jessa',
                'xp' => 41
            ], [
                'username' => 'cklucido',
                'password' => 'secret',
                'name' => 'kaye',
                'xp' => 145
            ], [
                'username' => 'joypintor',
                'password' => 'secret',
                'name' => 'jazarr',
                'xp' => 621
            ], [
                'username' => 'kharen',
                'password' => 'secret',
                'name' => 'karen',
                'xp' => 91
            ], [
                'username' => 'tinejoy',
                'password' => 'secret',
                'name' => 'tine',
                'xp' => 321
            ]
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
            'xp'
        ];

        $fetchSpies = $this->repo->fetch($attributes);

        $this->assertTrue(count($fetchSpies) > 0, 'No fetched record of spies.');

        foreach ($fetchSpies as $fetchSpy) {
            $this->assertEquals(array_keys($fetchSpy->getAttributes()), $attributes);
        }
    }

    public function seedForTestingMultipleSort()
    {
        $spies = [
            [
                'id'   => 1,
                'name' => 'anna',
                'xp'   => 2
            ],[
                'id'   => 2,
                'name' => 'anna',
                'xp'   => 3
            ],[
                'id'   => 3,
                'name' => 'anna',
                'xp'   => 4
            ],
        ];

        foreach ($spies as $spy) {
            $this->repo->create($spy);
        }
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

        $this->assertTrue($spy->getRelation("target") instanceof Target);
    }

    public function testShouldEagerLoadRelationsWithAttributes()
    {
        $this->seedWithTarget();

        $relations = [
            'target' => [
                'attributes' => [
                    'id',
                    'name'
                ]
            ]
        ];

        $spy = $this->repo->with($relations)->first();
        
        $this->assertTrue($spy->getRelation("target") instanceof Target);
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
