<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Discipline;
use App\Models\Meta;
use App\Models\Notion;
use App\Models\Report;
use App\Models\User;

/**
 * Class UserTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class UserTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Exception
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "admin",
            "count" => random_int(2, 4)
        ]);
        User::where("type", "admin")
            ->first()
            ->update(["email" => "admin@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "moderator",
            "count" => random_int(10, 20)
        ]);
        User::where("type", "moderator")
            ->first()
            ->update(["email" => "moderator@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "registered",
            "count" => random_int(40, 60)
        ]);
        User::where("type", "registered")
            ->first()
            ->update(["email" => "registered@mathrix.fr"]);

        $this->seedFromJson([
            "modelClass" => \Laravel\Passport\Client::class,
            "table" => "oauth_clients"
        ]);


        // Seed favorites
        $registered = User::where(["type" => "registered"])->get();
        $metas = [];
        /** @var User $user */
        foreach ($registered as $user) {
            $metaCount = random_int(0, 10);

            for ($i = 0; $i < $metaCount; $i++) {
                /** @var Class_ $class_ */
                $class_ = $user->class_;
                /** @var Discipline $discipline */
                $discipline = $user->class_->disciplines()->inRandomOrder()->first();
                /** @var Chapter $chapter */
                $chapter = $discipline->chapters()->inRandomOrder()->first();
                /** @var Notion $notion */
                $notion = $chapter->notions()->inRandomOrder()->first();
                /** @var Activity $activity */
                $activity = $notion->activities()->inRandomOrder()->first();

                $statusId = random_int(0, 2);

                $metas[] = [
                    "user_id" => $user->id,
                    "country_id" => $user->class_->country->id,
                    "class_id" => $class_->id,
                    "discipline_id" => $discipline->id,
                    "chapter_id" => $chapter->id,
                    "notion_id" => $notion->id,
                    "activity_id" => $activity->id,
                    "favorite" => (bool)random_int(0, 1),
                    "status" => $statusId == 0 ? null : ($statusId == 1 ? "completed" : "to_revise")
                ];
            }
        }

        $this->seedFromArray($metas, Meta::class, true);

        Report::unguard();
        $this->seedFromFactory(["modelClass" => Report::class]);
        Report::reguard();
    }
}
