<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'id' => null,
        'username' => $faker->username,
        'email' => $faker->unique()->safeEmail,
    ];
});
$factory->afterMaking(User::class, function (User $user) {
    static $id = 0;

    if (!isset($user->id)) {
        $user->id = ++$id;
    }
});
