<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Course;
use Railgun\Models\Notion;
use Railgun\Support\Silicate;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Course::class, static function (Faker $faker) {
    $name       = ucfirst($faker->words(3, true));
    $slug       = Str::slug($name);
    $visible    = $faker->boolean(90);
    $timestamps = Timestamps::factory();

    return [
        'name'            => $name,
        'slug'            => $slug,
        'content'         => Silicate::wall(),
        'order'           => 99,
        'premium'         => $faker->boolean(90),
        'visible'         => $visible,
        'seo_index'       => $visible ? $faker->boolean(90) : false,
        'seo_slug'        => $slug,
        'seo_keywords'    => implode(',', $faker->words(3)),
        'seo_description' => $faker->sentence,
        'notion_id'       => Notion::random()->id,
        'created_at'      => $timestamps->createdAt,
        'updated_at'      => $timestamps->updatedAt,
    ];
});
