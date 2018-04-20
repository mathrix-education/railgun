<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\Post;
use App\Models\User;
use Faker\Generator;

/**
 * PostFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Post::class, function (Generator $faker) {
    $title = substr($faker->sentence($faker->numberBetween(3, 5)), 0, -1);
    $created = $faker->dateTimeBetween("-30 days", "now");

    return [
        "title" => $title,
        "slug" => str_slug($title),
        "module_id" => Module::where("type", "course")->inRandomOrder()->first()->id,
        "category_id" => Category::inRandomOrder()->first()->id,
        "user_id" => User::where("type", "admin")->inRandomOrder()->first()->id,
        "created_at" => $created,
        "updated_at" => $faker->boolean(10) ? $faker->dateTimeBetween($created, "now") : $created,
    ];
});
