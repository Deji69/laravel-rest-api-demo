<?php

namespace Tests\Feature;

use App\Post;
use App\User;
use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    /** @var Collection|User[] */
    protected Collection $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = factory(User::class, 3)->create();
        Cache::shouldReceive('missing')
            ->with('users.all.warmDB')
            ->andReturn(false);
    }

    public function test_get_users(): void
    {
        $response = $this->getJson('/api/users');
        $response->assertOk();
        $response->assertJson(['data' => $this->users->toArray()]);
    }

    public function test_get_user_by_id(): void
    {
        foreach ($this->users as $user) {
            /** @var User $user */
            $response = $this->getJson("/api/users/$user->id");
            $response->assertOk();
            $response->assertJson(['data' => $user->toArray()]);
        }
    }

    public function test_get_user_by_id_not_found(): void
    {
        $response = $this->getJson('/api/users/99');
        $response->assertNotFound();
    }

    public function test_find_user_by_email(): void
    {
        /** @var User[] $users */
        $response = $this->json('get', '/api/users', ['email' => $this->users[1]->email]);
        $response->assertOk();
        $response->assertJson(['data' => $this->users[1]->toArray()]);
    }

    public function test_get_user_posts(): void
    {
        $userPosts = [];
        $this->users->each(function (User $user, $idx) use (&$userPosts) {
            $numPosts = ([3, 0, 5])[$idx];

            if ($numPosts > 0) {
                $posts = factory(Post::class, $numPosts)->create(['user_id' => $user->id]);
                $userPosts[$idx] = $posts->toArray();
            } else {
                $userPosts[$idx] = [];
            }
        });

        foreach ($this->users as $idx => $user) {
            $response = $this->json('get', "/api/users/$user->id/posts");
            $response->assertOk();
            $response->assertJson(['data' => $userPosts[$idx]]);
        }
    }
}
