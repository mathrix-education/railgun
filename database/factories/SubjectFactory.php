<?php

declare(strict_types=1);

use Railgun\Models\Notion;
use Railgun\Models\Subject;
use Railgun\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * @var Factory $factory
 */
$factory->define(Subject::class, static function (Generator $faker) {
    $name       = ucfirst($faker->word);
    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'order'      => 99,
        'visible'    => $faker->boolean(90),
        'notion_id'  => Notion::query()
            ->where('type', '=', 'course')
            ->firstOrFail()
            ->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
