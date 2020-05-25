<?php
namespace App\Contracts;

interface ApiProvider
{
    /**
     * Query comments for a post, returned as an array.
     *
     * @param int $postId The post to query comments for.
     * @return array|null Returns NULL if the query failed.
     */
    public function queryPostComments(int $postId): ?array;

    /**
     * Query posts, returned as an array.
     *
     * @return array|null Returns NULL if the query failed.
     */
    public function queryPosts(): ?array;

    /**
     * Query users, returned as an array.
     *
     * @return array|null Returns NULL if the query failed.
     */
    public function queryUsers(): ?array;
}
