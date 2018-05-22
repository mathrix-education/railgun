<?php

use App\Models\BaseModel;
use App\Models\User;
use App\Models\Vote;
use Faker\Generator;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * VoteFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Vote::class, function (Generator $faker) {
    $type = $faker->randomElement(Vote::VOTABLE_TYPES);
    /** @var BaseModel $class */
    $class = Relation::getMorphedModel($type);

    do {
        $voteData = [
            "value" => $faker->boolean(70) ? 1 : -1,
            "user_id" => $faker->randomElement(User::all()->pluck("id")->toArray()),
            "votable_id" => $faker->randomElement($class::all()->pluck("id")->toArray()),
            "votable_type" => $type,
            "created_at" => $faker->dateTimeBetween("-1 month")
        ];
    } while (Vote::where([
        ["user_id", $voteData["user_id"]],
        ["votable_id", $voteData["votable_id"]],
        ["votable_type", $voteData["votable_type"]]
    ])->exists());

    return $voteData;
});
