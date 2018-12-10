<?php

use App\Models\Comment;
use App\Models\Path;
use App\Models\User;
use App\Utils\Tree;
use Faker\Generator;

/**
 * CommentFactory
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Comment::class, function (Generator $faker) {
    $created = $faker->dateTimeBetween("-1 month");
    $tree = new Tree();
    $path = Path::retrieve($tree->flatIds());

    return [
        "content" => $faker->text(),
        "parent_id" => null,
        "user_id" => User::random()->id,
        "module_id" => $path->module->id,
        "path_id" => $path->id,
        "created_at" => $created,
        "updated_at" => $faker->boolean(90) ? $created : $faker->dateTimeBetween($created, "now"),
    ];
});

/**
 * CommentChildFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Comment::class, "child", function () use ($factory) {
    $parent = Comment::inRandomOrder()
        ->whereNull("parent_id")
        ->firstOrFail();

    return array_merge($factory->raw(Comment::class), ["parent_id" => $parent->id]);
});
