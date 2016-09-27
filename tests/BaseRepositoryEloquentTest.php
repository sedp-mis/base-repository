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

    /**
     * @expectedException \ValidationException
     */
    public function testShouldFailWhenPasswordDidNotMatch()
    {
        $user = $this->repo->create([
            'username'              => 'ajcastro',
            'password'              => 'password',
            'name'                  => 'arjon',
            'email'                 => 'ajcastro29@gmail.com',
            'password'              => '123456',
            'password_confirmation' => '123459',
        ]);
    }

    public function testShouldCreateUser()
    {
        $user = $this->repo->create([
            'username'              => 'ajcastro',
            'password'              => 'password',
            'name'                  => 'arjon',
            'email'                 => 'ajcastro29@gmail.com',
            'password'              => '123456',
            'password_confirmation' => '123456',
        ]);

        $storedUser = User::find($user->id);
        $this->assertTrue($user instanceof User);
        $this->assertTrue($user->exists);
        $this->assertTrue($storedUser instanceof User);
        $this->assertEquals($storedUser->getAttributes(), $user->getAttributes());
    }

    public function testShouldUpdateUser()
    {
        $user = User::create([
            'username' => 'ajcastro',
            'password' => 'password',
            'name'     => 'arjon_x',
            'email'    => 'ajcastro29@gmail.com',
            'password' => '123456',
        ]);

        $updatedUser = $this->repo->update($user->id, ['name' => 'arjon']);

        $this->assertEquals('arjon', User::findOrFail($user->id)->name);
        $this->assertTrue($updatedUser instanceof User);
    }

    public function testShouldUpdateUserWithUniqueUsername()
    {
        $user = User::create([
            'username' => 'ajcastro',
            'password' => 'password',
            'name'     => 'arjon',
            'email'    => 'ajcastro29@gmail.com',
            'password' => '123456',
        ]);

        $updatedUser = $this->repo->update($user->id, ['username' => 'ajcastro29']);

        $this->assertEquals($updatedUser->getAttributes(), User::findOrFail($user->id)->getAttributes());
        $this->assertTrue($updatedUser instanceof User);
    }

    public function testShouldCreateUserWithoutValidation()
    {
        $model = new User;
        $model->setRules([]);
        $repo = (new BaseRepositoryEloquent)->setModel($model);

        $user = $repo->create([
            'username'              => 'ajcastro',
            'password'              => 'password',
            'name'                  => 'arjon',
            'email'                 => 'ajcastro29@gmail.com',
            'password'              => '123456',
            'password_confirmation' => '123456',
        ]);

        $storedUser = User::find($user->id);
        $this->assertTrue($user instanceof User);
        $this->assertTrue($user->exists);
        $this->assertEquals($storedUser->getAttributes(), $user->getAttributes());
    }

    public function testShouldCreateUserUsingSave()
    {
        $user = new User([
            'username'              => 'ajcastro',
            'password'              => 'password',
            'name'                  => 'arjon',
            'email'                 => 'ajcastro29@gmail.com',
            'password'              => '123456',
            'password_confirmation' => '123456',
        ]);

        $model = new User;
        $model->setRules([]);
        $repo = (new BaseRepositoryEloquent)->setModel($model);

        $repo->save($user);

        $storedUser = User::find($user->id);

        $this->assertTrue($user->exists);
        $this->assertEquals($storedUser->getAttributes(), $user->getAttributes());
    }

    public function testShouldUpdateUserUsingSave()
    {
        $user = User::create([
            'username'              => 'ajcastro',
            'password'              => 'password',
            'name'                  => 'arjon_x',
            'email'                 => 'ajcastro29@gmail.com',
            'password'              => '123456',
            'password_confirmation' => '123456',
        ]);

        $user->name     = 'arjon';
        $user->username = 'arjonyehey';

        $this->repo->save($user);

        $this->assertEquals(User::find($user->id)->getAttributes(), $user->getAttributes());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldFailWhenTryToPassMultipleItemsOnCreate()
    {
        $this->repo->create([
            [
                'username' => 'user1',
            ],[
                'username' => 'user2',
            ],
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldFailWhenTryToPassMultipleItemsOnSave()
    {
        $this->repo->save([
            [
                'username' => 'user1',
            ],[
                'username' => 'user2',
            ],
        ]);
    }
}
