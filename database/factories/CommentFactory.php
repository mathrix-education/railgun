<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Comment;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\User;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Comment::class, static function (Generator $faker) {
    $class = $faker->randomElement([Course::class, Exercise::class, Flashcard::class, Exam::class]);
    /** @var Course|Exercise|Flashcard|Exam $model */
    $model      = $class::random();
    $timestamps = Timestamps::factory();

    return [
        'content'          => $faker->text(),
        'parent_id'        => null,
        'user_id'          => User::random()->id,
        'commentable_type' => Str::slug(class_basename($model), '_'),
        'commentable_id'   => $model->id,
        'created_at'       => $timestamps->createdAt,
        'updated_at'       => $timestamps->updatedAt,
    ];
});

/**
 * @var Factory $factory
 */
$factory->defineAs(Comment::class, 'child', static function () use ($factory) {
    /** @var Comment $parent */
    $parent     = Comment::query()
        ->inRandomOrder()
        ->whereNull('parent_id')
        ->firstOrFail();
    $timestamps = Timestamps::factory($parent->created_at);

    return array_merge($factory->raw(Comment::class), [
        'parent_id'        => $parent->id,
        'commentable_type' => $parent->commentable_type,
        'commentable_id'   => $parent->commentable_id,
        'created_at'       => $timestamps->createdAt,
        'updated_at'       => $timestamps->updatedAt,
    ]);
});
