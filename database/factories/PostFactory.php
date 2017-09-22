<?php
use App\Models\Category;
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
    $created = $faker->dateTime();

    return [
        'title' => $title,
        'slug' => str_slug($title),
        'content' => $faker->text(),
        'category_id' => $faker->randomElement(Category::all()->pluck('id')->toArray()),
        'user_id' => $faker->randomElement(User::all()->pluck('id')->toArray()),
        'created_at' => $created,
    ];
});