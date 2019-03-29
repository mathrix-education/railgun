<?php

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Log;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Bases\BaseTableSeeder;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class LogTableSeeder.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.0.3
 */
class LogTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http400BadRequestException
     */
    public function run()
    {
        $faker = FakerFactory::create();
        $users = User::query()
            ->whereNotNull("grade_id")
            ->get();

        $today = Carbon::now();
        $logs = new Collection();

        $this->output->writeln("<comment>Generating:</comment> logs from {$users->count()} users");
        $this->output->progressStart($users->count());

        /** @var User $user */
        foreach ($users as $user) {
            /** @var Carbon $dateIterator */
            $dateIterator = $user->created_at->lessThan(Carbon::now()
                ->subMonth()) ? Carbon::now()
                ->subMonth() :
                $user->created_at;

            while ($dateIterator->lessThan($today)) {
                $dateIterator->setTime(
                    $faker->numberBetween(0, 23),
                    $faker->numberBetween(0, 59),
                    $faker->numberBetween(0, 59)
                );

                // 70% chance to gather points on the given day
                if ($faker->numberBetween(0, 10) < 5) {
                    $points = 10 * $faker->numberBetween(1, 9);

                    for ($i = 0; $i < $points; $i += 10) {
                        $logs->push(
                            Log::build("user_gathered_points", $user, [
                                "points" => $points,
                                "loggable_type" => "module",
                                "loggable_id" => Module::random()->id,
                                "created_at" => $dateIterator,
                            ])->getAttributes()
                        );
                    }
                }

                $dateIterator->addDay(1);
            }

            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $this->output->writeln("<info>Generated:</info> {$logs->count()} logs");

        // Seed generated array
        $this->output->writeln("<comment>Seeding:</comment> logs");
        $this->seedFromArray($logs->toArray(), "logs");
        $this->output->writeln("<info>Seeded:</info> logs");
    }
}
