<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Mark;
use App\Models\Module;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Zero\Database\BaseTableSeeder;

class MarksTableSeeder extends BaseTableSeeder
{
    public function run(): void
    {
        $faker      = FakerFactory::create();
        $registered = User::all();
        $marks      = new Collection();

        $this->output->writeln('<comment>Generating:</comment> marks');
        $progressBar = $this->output->createProgressBar($registered->count());
        $progressBar->setFormat(BaseTableSeeder::DEFAULT_PROGRESS_BAR_FORMAT);
        $progressBar->start();

        /** @var User $user */
        foreach ($registered as $user) {
            $marksCount = $faker->numberBetween(30, 50);
            for ($i = 0; $i < $marksCount; ++$i) {
                $markData = [
                    'markable_type' => $faker->boolean ? 'module' : 'activity',
                ];

                if ($markData['markable_type'] === 'activity') {
                    /** @var Activity $model */
                    $model = Activity::random(['visible', '=', true]);
                } else {
                    /** @var Module $model */
                    $model = Module::random(['visible', '=', true]);
                }

                [$createdAt, $updatedAt] = timestamps($model->created_at->__toString());

                $markData = array_merge(
                    $markData,
                    [
                        'status'      => $faker->randomElement(Mark::STATUSES),
                        'markable_id' => $model->id,
                        'user_id'     => $user->id,
                        'created_at'  => $createdAt,
                        'updated_at'  => $updatedAt,
                    ]
                );
                $marks->push($markData);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->output->writeln('');

        // See generated array
        $this->output->writeln('<comment>Seeding:</comment> marks');
        $this->seedFromArray($marks->toArray(), 'marks');
    }
}
