<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Post;
use App\Comment;
use Faker\Generator as Faker;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'post_id' => factory(Post::class),
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'body' => $faker->text(400),
    ];
});
$factory->state(Comment::class, 'nopost', [
    'post_id' => null,
]);
