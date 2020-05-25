<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Post;
use App\User;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'id' => null,
        'user_id' => factory(User::class),
        'title' => $faker->sentence(6, true),
        'body' => $faker->text(400),
    ];
});
$factory->state(Post::class, 'nouser', [
    'user_id' => null,
]);
$factory->afterMaking(Post::class, function (Post $post) {
    static $id = 0;

    if (!isset($post->id)) {
        $post->id = ++$id;
    }
});
