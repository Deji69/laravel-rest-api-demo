<?php

namespace App\Repositories;

use App\Post;
use App\Contracts\ApiProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

class PostRepository
{
    protected const CACHE_TTL = 5 * 60;
    protected ApiProvider $api;

    public function __construct(ApiProvider $api)
    {
        $this->api = $api;
    }

    /**
     * Get all posts.
     *
     * @param int|null $limit
     * @return Builder
     */
    public function all(): Builder
    {
        $this->warmCache();
        return Post::query();
    }

    /**
     * Find post by ID.
     *
     * @param int $id
     * @return Post|null
     */
    public function find(int $id): ?Post
    {
        $this->warmCache();
        return Post::find($id);
    }

    /**
     * Search for posts by title.
     *
     * @param string $title
     * @param int|null $limit
     * @return Builder
     */
    public function searchByTitle(string $title, int $limit = null): Builder
    {
        $this->warmCache();
        return Post::where('title', 'like', '%'.addcslashes($title, '%').'%')
            ->limit($limit);
    }

    /**
     * Use to warm the posts cache.
     *
     * @param bool $force Warm the cache even if it hasn't gone cold.
     * @return void
     */
    public function warmCache(bool $force = false)
    {
        // We want to hit up the DB for the cache, so only use Laravel Caching
        // to handle the invalidation.
        if ($force || Cache::missing('posts.all.warmDB')) {
            DB::table('posts')->truncate();
            DB::table('comments')->truncate();

            $posts = collect($this->api->queryPosts());
            $comments = collect();

            // Cache all posts in DB
            $data = $posts->map(function ($post) use (&$comments) {
                // Append the comments for this post to the array of comments
                // to also be cached.
                $comments = $comments->merge($this->api->queryPostComments($post['id']));

                return [
                    'user_id' => $post['userId'],
                    'title' => $post['title'],
                    'body' => $post['body'],
                ];
            });

            if ($data->isNotEmpty()) {
                DB::table('posts')->insert($data->toArray());
            }

            // Cache all post comments in DB
            $data = $comments->map(function ($comment) {
                return [
                    'post_id' => $comment['postId'],
                    'name' => $comment['name'],
                    'email' => $comment['email'],
                    'body' => $comment['body'],
                ];
            });

            if ($data->isNotEmpty()) {
                DB::table('comments')->insert($data->toArray());
            }

            Cache::set('posts.all.warmDB', true, static::CACHE_TTL);
        }
    }
}
