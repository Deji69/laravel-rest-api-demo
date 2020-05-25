<?php

namespace Tests\Unit\Services;

use Tests\CreatesApplication;
use PHPUnit\Framework\TestCase;
use App\Services\JsonPlaceholder;
use Illuminate\Support\Facades\Http;

class JsonPlaceholderTest extends TestCase
{
    use CreatesApplication;

    /** @var \App\Contracts\ApiProvider */
    protected $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        $this->api = new JsonPlaceholder();
    }

    public function test_query_users()
    {
        Http::fake([
            'jsonplaceholder.typicode.com/users' => Http::response(['foo' => 'bar'], 200),
            '*' => Http::response(null, 404)
        ]);
        $result = $this->api->queryUsers();
        $this->assertSame(['foo' => 'bar'], $result);
    }

    public function test_query_posts()
    {
        Http::fake([
            'jsonplaceholder.typicode.com/posts' => Http::response(['foo' => 'bar'], 200),
            '*' => Http::response(null, 404)
        ]);
        $result = $this->api->queryPosts();
        $this->assertSame(['foo' => 'bar'], $result);
    }

    public function test_query_post_comments()
    {
        Http::fake([
            'jsonplaceholder.typicode.com/post/42/comments' => Http::response(['foo' => 'bar'], 200),
            '*' => Http::response(null, 404)
        ]);
        $result = $this->api->queryPostComments(42);
        $this->assertSame(['foo' => 'bar'], $result);
    }
}
