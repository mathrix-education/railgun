<?php
namespace Database\seeds;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Log;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class LogTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Database\seeds
 */
class LogTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    public function run()
    {
        $users = User::whereNotNull("class_id")
            ->get();
        $today = Carbon::now();
        $logs = [];
        $counter = 0;

        $this->output->writeln("<comment>Generating:</comment> logs");

        foreach ($users as $user) {
            /** @var User $user */
            /** @var Carbon $dateIterator */
            $dateIterator = $user->created_at->lessThan(Carbon::now()
                ->subMonth()) ? Carbon::now()
                ->subMonth() :
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
                        } while ($module->activities()
                            ->get()
                            ->isEmpty());

                        $logs[] = Log::build("user_gathered_points", $user, [
                            "points" => $points,
                            "loggable_type" => "module",
                            "loggable_id" => $module->id,
                            "created_at" => $dateIterator,
                        ])
                            ->getAttributes();
                        ++$counter;

                        if ($counter % 100 === 0) {
                            $this->output->write(".");
                        }
                    }
                }

                $dateIterator->addDay(1);
            }
        }
        $this->output->writeln("");
        $this->output->writeln("<info>Generated:</info> {$counter} logs");

        $this->output->writeln("<comment>Seeding:</comment> logs");
        $this->seedFromArray($logs, "logs");
        $this->output->writeln("<info>Seeded:</info> logs");
    }
}
