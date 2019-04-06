<?php

use App\Models\Comment;
use App\Models\Module;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * CommentFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->define(Comment::class, function (Generator $faker) {
    [$createdAt, $updatedAt] = timestamps();

    return [
        "content" => $faker->text(),
        "parent_id" => null,
        "user_id" => User::random()->id,
        "module_id" => Module::random(["visible", "=", true]),
        "created_at" => $createdAt,
        "updated_at" => $updatedAt,
    ];
});


/**
 * CommentChildFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->defineAs(Comment::class, "child", function () use ($factory) {
    /** @var Comment $parent */
    $parent = Comment::query()
        ->inRandomOrder()
        ->whereNull("parent_id")
        ->firstOrFail();

    return array_merge($factory->raw(Comment::class), [
        "parent_id" => $parent->id,
        "module_id" => $parent->module_id
    ]);
});
