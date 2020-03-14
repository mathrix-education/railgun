<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Category;
use Railgun\Models\Post;
use Railgun\Support\Silicate;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Post::class, static function (Faker $faker) {
    $title      = ucfirst($faker->words(3, true));
    $timestamps = Timestamps::factory();

    return [
        'title'       => $title,
        'slug'        => Str::slug($title),
        'content'     => Silicate::wall(),
        'order'       => 99,
        'visible'     => $faker->boolean(90),
        'category_id' => Category::random()->id,
        'created_at'  => $timestamps->createdAt,
        'updated_at'  => $timestamps->updatedAt,
    ];
});
