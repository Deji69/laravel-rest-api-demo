<?php

namespace Tests\Feature;

use App\Post;
use App\Comment;
use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostsTest extends TestCase
{
    use RefreshDatabase;

    /** @var Collection|Post[] */
    protected Collection $posts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->posts = factory(Post::class, 3)->create();
        Cache::shouldReceive('missing')
            ->with('posts.all.warmDB')
            ->andReturn(false);
    }

    public function test_get_posts(): void
    {
        $response = $this->getJson('/api/posts');
        $response->assertOk();
        $response->assertJson(['data' => $this->posts->toArray()]);
    }

    public function test_get_post_by_id(): void
    {
        foreach ($this->posts as $post) {
            /** @var Post $post */
            $response = $this->getJson('/api/posts/'.$post->id);
            $response->assertOk();
            $response->assertJson(['data' => $post->toArray()]);
        }
    }

    public function test_get_post_by_id_not_found(): void
    {
        $response = $this->getJson('/api/posts/99');
        $response->assertNotFound();
    }

    public function test_get_post_comments(): void
    {
        $postComments = [];
        $this->posts->each(function (Post $post, $idx) use (&$postComments) {
            $numComments = ([3, 0, 5])[$idx];

            if ($numComments > 0) {
                $comments = factory(Comment::class, $numComments)->create(['post_id' => $post->id]);
                $postComments[$idx] = $comments->toArray();
            } else {
                $postComments[$idx] = [];
            }
        });

        foreach ($this->posts as $idx => $post) {
            $response = $this->json('get', "/api/posts/$post->id/comments");
            $response->assertOk();
            $response->assertJson(['data' => $postComments[$idx]]);
        }
    }
}
