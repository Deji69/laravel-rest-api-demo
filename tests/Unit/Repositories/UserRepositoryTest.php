<?php

namespace Tests\Unit\Repositories;

use App\User;
use Mockery as m;
use Tests\TestCase;
use App\Contracts\ApiProvider;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_warms_cache()
    {
        Cache::shouldReceive('missing')
            ->once()
            ->with('users.all.warmDB')
            ->andReturn(true);

        Cache::shouldReceive('set')
            ->once()
            ->with('users.all.warmDB', true, m::any())
            ->andReturn(true);

        $data = factory(User::class, 3)->make()->toArray();
        $users = $this->makeRepository($data);
        $users->warmCache();

        $this->assertDatabaseHas('users', $data[0]);
        $this->assertDatabaseHas('users', $data[1]);
        $this->assertDatabaseHas('users', $data[2]);

        Cache::shouldReceive('set')
            ->once()
            ->with('users.all.warmDB', true, m::any())
            ->andReturn(true);

        $users = $this->makeRepository([]);
        $users->warmCache(true);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_does_not_warm_warmed_cache()
    {
        $users = $this->makeWarmedRepository();
        $users->warmCache();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_get_all(): void
    {
        $users = factory(User::class, 5)->create();
        $repository = $this->makeWarmedRepository();
        $result = $repository->all();
        $this->assertEquals($users->toArray(), $result->get()->toArray());
    }

    public function test_find_by_id(): void
    {
        $users = factory(User::class, 3)->create();
        $repository = $this->makeWarmedRepository();
        $result = $repository->find(2);
        $this->assertEquals($users[1]->toArray(), $result->toArray());
    }

    public function test_find_by_email(): void
    {
        $users = factory(User::class, 3)->create();
        $repository = $this->makeWarmedRepository();
        $result = $repository->findByEmail($users[1]->email);
        $this->assertEquals($users[1]->toArray(), $result->toArray());
    }

    private function makeRepository($providerData = []): UserRepository
    {
        $provider = m::mock(ApiProvider::class)
            ->shouldReceive('queryUsers')
            ->andReturn($providerData)
            ->mock();
        return new UserRepository($provider);
    }

    private function makeWarmedRepository(): UserRepository
    {
        Cache::shouldReceive('missing')
            ->once()
            ->with('users.all.warmDB')
            ->andReturn(false);

        $provider = m::mock(ApiProvider::class)
            ->shouldNotReceive('queryUsers')
            ->mock();
        return new UserRepository($provider);
    }
}
