<?php

namespace Tests\API\Comments;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Comment;
use App\Notifications\CommentAnswerNotification;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Laravel\Passport\Passport;

/**
 * Class CommentsAsPublicTest
 * Test Comments as registered user.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class CommentsAsRegisteredTest extends CommentsAsPublicTest
{
    /**
     * POST /comments.
     */
    public function testAdd()
    {
        $data = factory(Comment::class)->raw();
        unset($data["user_id"], $data["created_at"], $data["updated_at"]);

        $this->call("post", "/comments", $data);

        $expectedData = array_merge($data, ["user_id" => $this->user->id]);
        $this->assertInDatabase("comments", $expectedData);
        $this->assertHttp200Success();
    }

    public function testAddChild()
    {
        \Illuminate\Support\Facades\Notification::fake();

        $data = factory(Comment::class, "child")->raw();
        $parentComment = Comment::find($data["parent_id"]);
        unset($data["user_id"], $data["created_at"], $data["updated_at"]);

        $this->json("post", "/comments", $data);

        $expectedData = array_merge($data, ["user_id" => $this->user->id]);

        $this->assertHttp200Success();
        $this->assertInDatabase("comments", $expectedData);
        $this->assertNotificationSentToVia(
            CommentAnswerNotification::class,
            $parentComment->user,
            "database"
        );

    }

    /**
     * POST /comments.
     */
    public function testAddForStranger()
    {
        $stranger = $this->stranger();

        $data = factory(Comment::class)->raw();
        $data["user_id"] = $stranger->id;
        unset($data["created_at"], $data["updated_at"]);

        $this->json("post", "/comments", $data);

        $this->assertHttp401Unauthorized();
        $this->assertNotInDatabase("comments", $data);
    }

    /**
     * PATCH /comments/{id}.
     *
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function testEditFresh()
    {
        $comment = Comment::random();
        $comment->created_at = Carbon::now()->subDay();
        $comment->save();
        $user = $comment->user;
        Passport::actingAs($user, [$user->type]);
        $comment->content = $this->faker->sentence;

        $this->call("patch", "/comments/{$comment->id}", [
            "content" => $this->faker->sentence,
        ]);

        $this->assertHttp200Success();
    }
}
