<?php

namespace Tests\API\Comments;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Comment;
use App\Notifications\CommentAnswerNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;
use App\Utils\Tree;

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
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $data = factory(Comment::class)->raw();
        $tree = new Tree();
        $data["path"] = $tree->flatIds();
        unset($data["user_id"], $data["created_at"], $data["updated_at"]);

        $this->json("post", "/comments", $data);

        unset($data["path"]);
        $pathId = json_decode($this->response->getContent())->path_id;
        $expectedData = array_merge($data, [
            "user_id" => $this->user->id,
            "path_id" => $pathId
        ]);

        $this->assertInDatabase("comments", $expectedData);
        $this->assertHttp200Success();
    }

    /**
     * POST /comments
     *
     * @throws \Exception
     */
    public function testAddChild()
    {
        Notification::fake();

        $data = factory(Comment::class, "child")->raw();
        $parentComment = Comment::find($data["parent_id"]);
        $tree = new Tree();
        $data["path"] = $tree->flatIds();
        unset($data["user_id"], $data["created_at"], $data["updated_at"]);

        $this->json("post", "/comments", $data);

        unset($data["path"]);
        $pathId = json_decode($this->response->getContent())->path_id;
        $expectedData = array_merge($data, [
            "user_id" => $this->user->id,
            "path_id" => $pathId
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("comments", $expectedData);
        $this->assertNotificationSentToVia(
            CommentAnswerNotification::class,
            $parentComment->user,
            "database"
        );
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

        $this->json("patch", "/comments/{$comment->id}", [
            "content" => $this->faker->sentence,
        ]);

        $this->assertHttp200Success();
    }
}
