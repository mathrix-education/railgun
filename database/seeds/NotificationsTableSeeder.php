<?php

use App\Models\Comment;
use App\Models\User;
use Faker\Factory;

/**
 * Class NotificationsTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 * @package   Database\seeds
 */
class NotificationsTableSeeder extends BaseTableSeeder
{
    /** @var Faker\Generator */
    private $faker;
    private $counter = 0;
    private $notifications;

    public function run()
    {
        $this->faker = Factory::create();

        $this->output->writeln("<comment>Generating:</comment> notifications");
        $parents = Comment::has("children")
            ->orderBy("created_at")
            ->get();

        /** @var Comment $parent */
        foreach ($parents as $parent) {
            /** @var Comment $child */
            foreach ($parent->children as $child) {
                /** @var Comment $childComment */
                $childComment = $child;
                /** @var Comment $parentComment */
                $parentComment = $childComment->parent;

                // Notify the parent comment author
                $parentAuthor = $parentComment->user;
                $this->generateNotification($parentAuthor, $childComment);

                // Notify the comment children authors who posted before the event's comment
                $childrenBefore = $parentComment->children()
                    ->where("created_at", "<", $childComment->created_at)
                    ->get();

                /** @var Comment $comment */
                foreach ($childrenBefore as $comment) {
                    $this->generateNotification($comment->user, $childComment);
                }
            }
        }

        $this->output->writeln("");
        $this->output->writeln("<info>Generated:</info> {$this->counter} notifications");

        $this->output->writeln("<comment>Seeding:</comment> notifications");
        $this->seedFromArray($this->notifications, "notifications");
        $this->output->writeln("<info>Seeded:</info> notifications");
    }

    /**
     * Generate a notification database line
     *
     * @param User    $target
     * @param Comment $comment
     */
    public function generateNotification(User $target, Comment $comment)
    {
        $comment->user->makeHidden(["email"]);

        // Unset unwanted relations
        unset($comment->parent);

        $data = $comment->toArray();
        $data["path"] = $comment->path;

        $notificationsData = [
            "id" => $this->faker->uuid,
            "type" => "comment_answer",
            "notifiable_type" => "user",
            "notifiable_id" => $target->id,
            "data" => json_encode($data),
            "read_at" => $this->faker->boolean(30) ?
                $this->faker->dateTimeBetween($comment->created_at, 'now') : null,
            "created_at" => $comment->created_at,
            "updated_at" => $comment->created_at
        ];

        $this->notifications[] = $notificationsData;

        $this->counter++;
        if ($this->counter % 100 === 0) {
            $this->output->write(".");
        }
    }
}
