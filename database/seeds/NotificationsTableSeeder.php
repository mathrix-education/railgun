<?php

use App\Models\Comment;
use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Bases\BaseTableSeeder;

/**
 * Class NotificationsTableSeeder.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.2.0
 */
class NotificationsTableSeeder extends BaseTableSeeder
{
    /** @var Faker\Generator */
    private $faker;
    /** @var Collection */
    private $notifications;


    public function run()
    {
        $this->faker = Factory::create();
        $this->notifications = new Collection();
        $parents = Comment::query()
            ->has("children")
            ->orderBy("created_at")
            ->get();

        $this->output->writeln("<comment>Generating:</comment> notifications");
        $this->output->progressStart($parents->count());
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
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $this->output->writeln("<info>Generated:</info> {$this->notifications->count()} notifications");

        // Seed generated array
        $this->output->writeln("<comment>Seeding:</comment> notifications");
        $this->seedFromArray($this->notifications->toArray(), "notifications");
        $this->output->writeln("<info>Seeded:</info> notifications");
    }


    /**
     * Generate a notification database line
     *
     * @param User $target
     * @param Comment $comment
     */
    public function generateNotification(User $target, Comment $comment)
    {
        $comment->user->makeHidden(["email"]);

        // Unset unwanted relations
        unset($comment->parent);
        $data = $comment->toArray();

        $notificationsData = [
            "id" => $this->faker->uuid,
            "type" => "comment_answer",
            "notifiable_type" => "user",
            "notifiable_id" => $target->id,
            "data" => json_encode($data),
            "read_at" => $this->faker->boolean(30) ?
                $this->faker->dateTimeBetween($comment->created_at) : null,
            "created_at" => $comment->created_at,
            "updated_at" => $comment->created_at
        ];

        $this->notifications->push($notificationsData);
    }
}
