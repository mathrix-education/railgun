<?php

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Discipline;
use App\Models\Log;
use App\Models\Meta;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;

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
            "count" => 2
        ]);
        User::where("type", "admin")
            ->first()
            ->update(["email" => "admin@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "moderator",
            "count" => random_int(2, 4)
        ]);
        User::where("type", "moderator")
            ->first()
            ->update(["email" => "moderator@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "registered",
            "count" => random_int(10, 15)
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
                    "completed" => $statusId == 0,
                    "to_revise" => $statusId == 1
                ];
            }
        }

        $this->seedFromArray($metas, Meta::class, true);

        // Simulate points
        Report::unguard();
        $this->seedFromFactory(["modelClass" => Report::class]);
        Report::reguard();

        $this->simulatePoints();
    }


    /**
     * Simulate user points gathering
     *
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function simulatePoints()
    {
        $users = User::whereNotNull("class_id")->get();
        $today = Carbon::now();
        $logs = [];
        $counter = 0;

        $this->println("<comment>Generating:</comment> logs");
        $this->output->write("Logs generated: 0");

        foreach ($users as $user) {
            /** @var User $user */
            /** @var Carbon $dateIterator */
            $dateIterator = $user->created_at->lessThan(Carbon::now()->subMonth()) ? Carbon::now()->subMonth() :
                $user->created_at;

            while ($dateIterator->lessThan($today)) {
                $dateIterator->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59));

                // 70% chance to gather points on the given day
                if (random_int(0, 10) < 7) {
                    $points = 10 * random_int(1, 9);

                    for ($i = 0; $i < $points; $i += 10) {
                        /** @var Module $module */
                        $module = null;

                        do {
                            $module = Module::random();
                        } while ($module->activities()->get()->isEmpty());

                        $logs[] = Log::build("user_gathered_points", $user, [
                            "value" => $points,
                            "loggable_type" => "module",
                            "loggable_id" => $module->id,
                            "created_at" => $dateIterator
                        ])->toArray();
                        $counter++;
                        $this->overwrite("Logs generated: $counter");
                    }
                }

                $dateIterator->addDay(1);
            }
        }
        $this->println("");
        $this->println("<info>Generated:</info> logs");

        $this->println("<comment>Seeding:</comment> logs");
        $this->seedFromArray($logs, Log::class);
        $this->println("<info>Seeded:</info> logs");
    }
}
