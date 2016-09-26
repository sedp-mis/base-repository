<?php

// Sample URL
// http://localhost:8000/api/v1/posts?relations[]=label&relations[comments][attributes][]=id&relations[comments][attributes][]=text

use SedpMis\BaseRepository\BaseRepositoryEloquent;

class BaseRepositoryEloquentTest extends TestCase
{
    protected $repo;

    public function setUp()
    {
        parent::setUp();

        Artisan::call('migrate');

        DB::beginTransaction();

        $this->repo = (new BaseRepositoryEloquent)->setModel(new User);
    }

    public function tearDown()
    {
        parent::tearDown();

        DB::rollback();
    }

    public function testShouldCreateStoreFindUpdateAndDeleteModel()
    {
        $user = $this->repo->create([
            'username' => 'ajcastro',
            'password' => 'password',
            'name' => 'arjon',
            'email' => 'ajcastro29@gmail.com'
        ]);


        $storedUser = $this->repo->find($user->id);
        
        $this->assertTrue($user instanceof User);
        $this->assertTrue($storedUser instanceof User);
        $this->assertEquals($storedUser->getAttributes(), $user->getAttributes());

        $storedUser->name = 'ajcastro';

        $this->repo->update(['name' => $storedUser->name], $storedUser->id);

        $updatedUser = $this->repo->find($storedUser->id);
        $this->assertEquals($updatedUser->getAttributes(), $storedUser->getAttributes());

        $this->repo->delete($user);

        $this->assertEquals(0, User::count());
    }
}
