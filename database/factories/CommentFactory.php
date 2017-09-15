<?php
use App\Models\Comment;
use App\Models\User;
use Faker\Generator;

/**
 * CommentFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Comment::class, function (Generator $faker) {
    $commentable_type = $faker->randomElement(['module', 'post'/*, 'course'*/]);
    $commentable_class = 'App\\Models\\' . ucfirst($commentable_type);
    $created = $faker->dateTime();

    return [
        'content' => $faker->text(),
        'parent_id' => $faker->boolean(60) ? null : $faker->randomElement(
            Comment::where('parent_id', '=', null)->pluck('id')->toArray()
        ),
        'user_id' => $faker->randomElement(User::all()->pluck('id')->toArray()),
        'created_at' => $created,
        // TODO: Handle the +3 days
        'updated_at' => $faker->boolean(90) ? $created : $faker->dateTimeBetween($created, 'now'),
        'commentable_id' => $faker->randomElement($commentable_class::all()->pluck('id')->toArray()),
        'commentable_type' => $commentable_type
    ];
});