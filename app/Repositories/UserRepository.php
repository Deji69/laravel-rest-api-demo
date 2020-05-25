<?php

namespace App\Repositories;

use App\User;
use App\Contracts\ApiProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    protected const CACHE_TTL = 5 * 60;
    protected ApiProvider $api;

    public function __construct(ApiProvider $api)
    {
        $this->api = $api;
    }

    /**
     * Get all users.
     *
     * @param int|null $limit
     * @return Builder
     */
    public function all(int $limit = null): Builder
    {
        $this->warmCache();
        return User::limit($limit);
    }

    /**
     * Find user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User
    {
        $this->warmCache();
        return User::find($id);
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        $this->warmCache();
        return User::whereEmail($email)->first();
    }

    /**
     * Use to warm the users cache.
     *
     * @param bool $force Warm the cache even if it hasn't gone cold yet.
     * @return void
     */
    public function warmCache(bool $force = false)
    {
        // We want to hit up the DB for the cache, so only use Laravel Caching
        // to handle the invalidation.
        if ($force || Cache::missing('users.all.warmDB')) {
            DB::table('users')->truncate();

            $users = collect($this->api->queryUsers());

            $data = $users->map(function ($user) {
                return [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                ];
            });

            if ($data->isNotEmpty()) {
                DB::table('users')->insert($data->toArray());
            }

            Cache::set('users.all.warmDB', true, static::CACHE_TTL);
        }
    }
}
