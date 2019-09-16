<?php

declare(strict_types=1);

use App\Models\Module;
use App\Models\Report;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ReportFactory.
 *
 * @var Factory $factory
 */
$factory->define(Report::class, static function (Generator $faker) {
    /** @var Module $module */
    [$createdAt, $updatedAt] = timestamps();

    return [
        'context' => null,
        'type' => 'other',
        'content' => $faker->sentence(10),
        'status' => $faker->randomElement(Report::STATUSES),
        'module_id' => Module::random()->id,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];
});
