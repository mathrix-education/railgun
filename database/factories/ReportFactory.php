<?php

use App\Models\Module;
use App\Models\Report;
use Faker\Generator;

/**
 * QuoteFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Report::class, function (Generator $faker) {
    /** @var Module $module */
    $module = Module::random();
    $createdAt = $faker->dateTimeBetween("-1 years");

    return [
        "context" => null,
        "type" => "other",
        "content" => $faker->sentence(10),
        "status" => $faker->randomElement(Report::STATUSES),
        "module_id" => $module->id,
        "created_at" => $createdAt,
        "updated_at" => $faker->boolean(90) ? $createdAt : $faker->dateTimeBetween($createdAt, "now"),
    ];
});
