<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Notion;
use Railgun\Models\Subject;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Subject::class, static function (Faker $faker) {
    $name       = ucfirst($faker->word);
    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'slug'       => Str::slug($name),
        'order'      => 99,
        'premium'    => $faker->boolean(10),
        'visible'    => $faker->boolean(90),
        'notion_id'  => Notion::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
