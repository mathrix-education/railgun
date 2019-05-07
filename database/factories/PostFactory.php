<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\Post;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * NotionFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(Post::class, function (Generator $faker) {
    $title = mb_substr($faker->sentence($faker->numberBetween(3, 5)), 0, -1);
    [$createdAt, $updatedAt] = timestamps();

    return [
        "title" => $title,
        "slug" => Str::slug($title),
        "module_id" => Module::random(["type", "=", "course"])->id,
        "category_id" => Category::random()->id,
        "user_id" => User::random()->id,
        "created_at" => $createdAt,
        "updated_at" => $updatedAt,
    ];
});
