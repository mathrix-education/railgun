<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function collect;
use function count;
use function implode;
use function json_decode;
use function json_encode;
use function trim;
use const JSON_THROW_ON_ERROR;

class CoursesCommand extends V7Command
{
    private const CHUNK_SIZE = 100;

    protected $signature = 'v7:courses';

    public function handle(): void
    {
        $this->title('Migrating modules => courses');

        $query = DB::table('modules')
            ->where('modules.type', '=', 'course')
            ->where('modules.content', '!=', '')
            ->join('activities', static function (JoinClause $join) {
                $join->on('modules.activity_id', '=', 'activities.id')
                    ->where('activities.notion_id', '>', 0);
            })
            ->selectRaw(implode(',', [
                'modules.id AS id', // keep module ids, mapping is not necessary
                'activities.name AS name',
                'modules.content AS content',
                'modules.visible AS visible',
                'modules.seo_index AS seo_index',
                'modules.seo_title AS seo_title',
                'modules.seo_slug AS seo_slug',
                'modules.seo_description AS seo_description',
                'modules.seo_keywords AS seo_keywords',
                'activities.notion_id AS notion_id',
                'modules.created_at AS created_at',
                'modules.updated_at AS updated_at',
            ]));

        $errorTable = [];
        $courses    = $query->get()
            ->filter(static function ($module) use (&$errorTable) {
                $keep    = false;
                $content = json_decode($module->content, false, 512, JSON_THROW_ON_ERROR);

                if (empty($content->silicate) && !empty(trim($content->text))) {
                    $errorTable[] = [$module->id, 'not migrated'];
                } elseif (!isset($content->silicate)) {
                    // $errorTable[] = [$module->id, 'missing'];
                } elseif (empty($content->silicate)) {
                    // $errorTable[] = [$module->id, 'empty'];
                } else {
                    $keep = true;
                }

                return $keep;
            })
            ->map(static function ($module) {
                $content         = json_decode($module->content, false, 512, JSON_THROW_ON_ERROR);
                $module->content = json_encode($content->silicate, JSON_THROW_ON_ERROR, 512);

                return $module;
            })
            ->toArray();

        if (!empty($errorTable)) {
            // TODO: enforce the use of silicate
            $this->warn('The following modules are not migrated on Silicate:');
            $this->table(['id', 'reason'], $errorTable);
        }

        $this->line('Found ' . count($courses) . ' courses');
        $iterator = 0;
        $notionId = null;

        foreach ($courses as $key => $course) {
            $course->slug = Str::slug($course->name);

            if ($notionId !== $course->notion_id) {
                $iterator = 0;
                $notionId = $course->notion_id;
            }

            $course->order = $iterator;
            $courses[$key] = (array)$course;
            $iterator++;
        }

        collect($courses)->chunk(self::CHUNK_SIZE)->each(static function (Collection $exercises) {
            DB::table('courses')->insert($exercises->toArray());
        });
    }
}
