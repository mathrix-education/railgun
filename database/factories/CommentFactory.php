<?php

use App\Models\BaseModel;
use App\Models\Comment;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Relations\Relation;

/*
 * CommentFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Comment::class, function (Generator $faker) {
    $commentable_type = $faker->randomElement(["activity", "post"]);
    /** @var BaseModel|string $commentable_class */
    $commentable_class = Relation::getMorphedModel($commentable_type);
    $created = $faker->dateTimeBetween("-1 month");

    return [
        "content" => $faker->text(),
        "parent_id" => $faker->boolean(60) ? null : $faker->randomElement(
            Comment::whereNull("parent_id")->pluck("id")->toArray()
        ),
        "user_id" => $faker->randomElement(User::all()->pluck("id")->toArray()),
        "created_at" => $created,
        "updated_at" => $faker->boolean(90) ? $created : $faker->dateTimeBetween($created, "now"),
        "commentable_id" => $faker->randomElement($commentable_class::all()->pluck("id")->toArray()),
        "commentable_type" => $commentable_type,
    ];
});

/*
 * CommentChildFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Comment::class, "child", function () use ($factory) {
    $parent = Comment::whereNull("parent_id")->firstOrFail();

    return array_merge($factory->raw(Comment::class), ["parent_id" => $parent->id]);
});
