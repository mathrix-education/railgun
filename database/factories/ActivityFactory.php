<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Notion;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ActivityFactory.
 *
 * @var Factory $factory
 */
$factory->define(Activity::class, static function (Generator $faker) {
    [$createdAt, $updatedAt] = timestamps();

    return [
        'name'       => mb_substr($faker->sentence(), 0, -1),
        'type'       => $faker->randomElement(Activity::TYPES),
        'visible'    => $faker->boolean(95),
        'order'      => $faker->numberBetween(0, 100),
        'premium'    => $faker->boolean(30),
        'review'     => ['test' => 'lol'],
        'notion_id'  => Notion::random()->id,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];
});
