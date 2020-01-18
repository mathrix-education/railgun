<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Support\Facades\DB;
use stdClass;
use function array_diff;
use function array_intersect;
use function collect;
use function count;
use function sprintf;

class NotionsCommand extends V7Command
{
    protected $signature = 'v7:notions';

    public function handle(): void
    {
        $this->title('Duplicating notions for subjects');

        $table = DB::table('notions');

        $this->line('Reset notion types to "to-delete"');
        $table->update(['type' => 'to-delete']);

        $repartition = $this->getRepartition();
        $this->line(sprintf("\nDuplicating %s notions while updating subjects", count($repartition['both'])));
        $progress = $this->getOutput()->createProgressBar(count($repartition['both']));

        collect($repartition['both'])->each(static function ($originalNotionId) use ($progress) {
            $original = DB::table('notions')->find($originalNotionId);
            unset($original->id);
            $duplicatedNotionId = DB::table('notions')->insertGetId((array)$original);
            DB::table('subjects')
                ->where('notion_id', '=', $originalNotionId)
                ->update(['notion_id' => $duplicatedNotionId]);

            $progress->advance();
        });

        $progress->finish();
        $this->getOutput()->newLine();

        $repartition = $this->getRepartition();
        $this->line('Assigning new notion types');
        DB::table('notions')
            ->whereIn('id', $repartition['courses_only'])
            ->update(['type' => 'course']);
        DB::table('notions')
            ->whereIn('id', $repartition['subjects_only'])
            ->update(['type' => 'subject']);

        $this->line('Final repartition:');
        $finalRepartition = DB::table('notions')
            ->selectRaw('type, COUNT(type) as `count`')
            ->groupBy('type')
            ->get()
            ->map(fn(stdClass $stat) => [$stat->type, $stat->count])
            ->toArray();
        $this->table(['type', 'count'], $finalRepartition);
    }

    public function getRepartition(bool $print = true): array
    {
        // Mark course notions
        $courseNotions = DB::table('notions')
            ->join('courses', 'notions.id', '=', 'courses.notion_id')
            ->distinct()
            ->get(['notions.id'])
            ->pluck('id')
            ->toArray();

        // Mark subjects notions
        $subjectNotions = DB::table('notions')
            ->join('subjects', 'notions.id', '=', 'subjects.notion_id')
            ->distinct()
            ->get(['notions.id'])
            ->pluck('id')
            ->toArray();

        // Duplicate remaining notions
        $innerNotions       = array_intersect($courseNotions, $subjectNotions);
        $courseOnlyNotions  = array_diff($courseNotions, $innerNotions);
        $subjectOnlyNotions = array_diff($subjectNotions, $innerNotions);

        $repartition = [
            'courses'       => $courseNotions,
            'courses_only'  => $courseOnlyNotions,
            'subjects'      => $subjectNotions,
            'subjects_only' => $subjectOnlyNotions,
            'both'          => $innerNotions,
        ];

        if ($print) {
            $table = [];

            foreach ($repartition as $type => $ids) {
                $table[] = [$type, count($ids)];
            }

            $this->line('Current notions repartition:');
            $this->table(['type', 'count'], $table);
        }

        return $repartition;
    }
}
