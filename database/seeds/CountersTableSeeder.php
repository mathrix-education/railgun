<?php

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Notion;
use Mathrix\Lumen\Bases\BaseTableSeeder;


/**
 * Class CountersTableSeeder.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc3
 */
class CountersTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $activities = Activity::all();
        $notions = Notion::all();
        $chapters = Chapter::all();
        $total = $activities->count() + $notions->count() + $chapters->count();

        $this->output->writeln("<comment>Seeding:</comment> chapter, notion and activity counters");
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setFormat(BaseTableSeeder::DEFAULT_PROGRESS_BAR_FORMAT);

        foreach ($activities as $activity) {
            $activity->updateCounters();
            $progressBar->advance();
        }
        foreach ($notions as $notion) {
            if ($notion->chapter_id === null) {
                continue;
            }

            $notion->updateCounters();
            $progressBar->advance();
        }
        foreach ($chapters as $chapter) {
            $chapter->updateCounters();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->output->write("\n");
    }
}
