<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Folder;
use App\User;
use Faker\Generator as Faker;

$factory->define(Folder::class, function (Faker $faker) {
    return [
        'user_id' => 1, // Must reset this manually.
        'title' => $faker->word,
    ];
});
