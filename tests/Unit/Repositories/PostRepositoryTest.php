<?php

namespace Tests\Unit\Repositories;

use App\Post;
use Mockery as m;
use Tests\TestCase;
use App\Contracts\ApiProvider;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected PostRepository $posts;

    public function test_warms_cache()
    {
        Cache::shouldReceive('missing')
            ->once()
            ->with('posts.all.warmDB')
            ->andReturn(true);

        Cache::shouldReceive('set')
            ->once()
            ->with('posts.all.warmDB', true, m::any())
            ->andReturn(true);

        $commentData = collect([
            1 => [$this->fakePlaceholderComment(1, 1)],
            2 => [
                $this->fakePlaceholderComment(2, 2),
                $this->fakePlaceholderComment(3, 2),
            ],
            3 => [$this->fakePlaceholderComment(4, 3)],
        ]);

        $postData = [
            $this->fakePlaceholderPost(1, 1),
            $this->fakePlaceholderPost(2, 1),
            $this->fakePlaceholderPost(3, 2),
        ];

        $posts = $this->makeRepository($postData, $commentData->flatten(1));
        $posts->warmCache();

        foreach ($postData as $post) {
            $post['user_id'] = $post['userId'];
            unset($post['userId']);
            $this->assertDatabaseHas('posts', $post);
        }

        foreach ($commentData->flatten(1) as $comment) {
            $comment['post_id'] = $comment['postId'];
            unset($comment['postId']);
            $this->assertDatabaseHas('comments', $comment);
        }

        Cache::shouldReceive('set')
            ->once()
            ->with('posts.all.warmDB', true, m::any())
            ->andReturn(true);

        $posts = $this->makeRepository([]);
        $posts->warmCache(true);
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_does_not_warm_warmed_cache()
    {
        $posts = $this->makeWarmedRepository();
        $posts->warmCache();
        $this->assertDatabaseCount('posts', 0);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_get_all(): void
    {
        $posts = factory(Post::class, 5)->create();
        $repository = $this->makeWarmedRepository();
        $result = $repository->all();
        $this->assertEquals($posts->toArray(), $result->get()->toArray());
    }

    public function test_find_by_id(): void
    {
        $posts = factory(Post::class, 3)->create();
        $repository = $this->makeWarmedRepository();
        $result = $repository->find(2);
        $this->assertEquals($posts[1]->toArray(), $result->toArray());
    }

    public function test_search_by_title(): void
    {
        $posts = factory(Post::class)->createMany([
            ['title' => 'Foo Bar Foo'],
            ['title' => 'Foo Foo Foo'],
            ['title' => 'Bar Foo Foo'],
            ['title' => 'Foo Foo Bar'],
            ['title' => 'Foo Foo Baa'],
        ]);

        $repository = $this->makeWarmedRepository();
        $result = $repository->searchByTitle('Bar');

        $this->assertEquals([
            $posts[0]->toArray(),
            $posts[2]->toArray(),
            $posts[3]->toArray(),
        ], $result->get()->toArray());
    }

    private function makeRepository($postData = [], $commentData = []): PostRepository
    {
        $comments = collect($commentData);
        $mock = m::mock(ApiProvider::class);
        $mock->shouldReceive('queryPosts')->andReturn($postData);
        $mock->shouldReceive('queryPostComments')
            ->times(count($postData))
            ->andReturnUsing(function (int $postId) use ($comments) {
                return $comments->where('postId', $postId)->toArray();
            });
        /** @var \App\Contracts\ApiProvider $mock */
        return new PostRepository($mock);
    }

    private function makeWarmedRepository(): PostRepository
    {
        Cache::shouldReceive('missing')
            ->once()
            ->with('posts.all.warmDB')
            ->andReturn(false);

        $provider = m::mock(ApiProvider::class)
            ->shouldNotReceive('queryPosts')
            ->shouldNotReceive('queryComments')
            ->mock();
        return new PostRepository($provider);
    }

    private function fakePlaceholderPost($postId, $userId): array
    {
        return [
            'userId' => $userId,
            'id' => $postId,
            'title' => $this->faker->sentence(6, true),
            'body' => $this->faker->text(400),
        ];
    }

    private function fakePlaceholderComment($commentId, $postId): array
    {
        return [
            'postId' => $postId,
            'id' => $commentId,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'body' => $this->faker->text(400),
        ];
    }
}
