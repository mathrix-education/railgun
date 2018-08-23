<?php

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Log;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class LogTableSeeder.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 4.0.3
 *
 * @copyright Mathrix Education SA
 */
class LogTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Exception
     */
    public function run()
    {
        $users = User::whereNotNull("class_id")->get();
        $today = Carbon::now();
        $logs = [];
        $counter = 0;

        $this->println("<comment>Generating:</comment> logs");

        foreach ($users as $user) {
            /** @var User $user */
            /** @var Carbon $dateIterator */
            $dateIterator = $user->created_at->lessThan(Carbon::now()->subMonth()) ? Carbon::now()->subMonth() :
                $user->created_at;

            while ($dateIterator->lessThan($today)) {
                $dateIterator->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59));

                // 70% chance to gather points on the given day
                if (random_int(0, 10) < 5) {
                    $points = 10 * random_int(1, 9);

                    for ($i = 0; $i < $points; $i += 10) {
                        /** @var Module $module */
                        $module = null;

                        do {
                            $module = Module::random();
                        } while ($module->activities()->get()->isEmpty());

                        $logs[] = Log::build("user_gathered_points", $user, [
                            "points" => $points,
                            "loggable_type" => "module",
                            "loggable_id" => $module->id,
                            "created_at" => $dateIterator,
                        ])->toArray();
                        ++$counter;

                        if ($counter % 100 === 0) {
                            $this->output->write(".");
                        }
                    }
                }

                $dateIterator->addDay(1);
            }
        }
        $this->println("");
        $this->println("<info>Generated:</info> {$counter} logs");

        $this->println("<comment>Seeding:</comment> logs");
        $this->seedFromArray($logs, Log::class);
        $this->println("<info>Seeded:</info> logs");
    }
}
