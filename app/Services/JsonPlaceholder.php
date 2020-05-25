<?php

namespace App\Services;

use App\Contracts\ApiProvider;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class JsonPlaceholder implements ApiProvider
{
    private const BASE_URL = 'http://jsonplaceholder.typicode.com/';

    public function queryPostComments(int $postId): ?array
    {
        return $this->getJson("post/$postId/comments");
    }

    public function queryPosts(): ?array
    {
        return $this->getJson('posts');
    }

    public function queryUsers(): ?array
    {
        return $this->getJson('users');
    }

    /**
     * Query an endpoint and get the response.
     *
     * @param string $endpoint
     * @param array $query
     * @return Response
     */
    private function get(string $endpoint, array $query = []): Response
    {
        return Http::get(self::BASE_URL.$endpoint, $query);
    }

    /**
     * Query an endpoint and get the decoded JSON data as an array.
     *
     * @param string $endpoint
     * @param array $query
     * @return array|null Returns NULL on HTTP or JSON decoding failure.
     */
    private function getJson(string $endpoint, array $query = []): ?array
    {
        $response = $this->get($endpoint, $query);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }
}
