<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use function implode;
use function str_replace;

class ProductsCommand extends V7Command
{
    protected $signature = 'v7:products';

    public function handle(): void
    {
        $this->packs();
        $this->trainings();
        $this->sections();
        $this->lessons();
    }

    private function packs(): void
    {
        $this->title('Migrate products => packs');

        $iterator = -1;

        DB::table('products')
            ->where('type', '=', 'pack')
            ->selectRaw(implode(', ', [
                'name',
                'slug',
                'buyable',
                'CAST(price AS UNSIGNED) AS price',
                'visible',
                'created_at',
                'updated_at',
                "CONCAT('product-', id) AS mapping",
            ]))
            ->get()
            ->map(static function (stdClass $pack) use (&$iterator) {
                $pack->order = ++$iterator;

                return (array)$pack;
            })
            ->tap(function (Collection $packs) {
                $this->line("Found {$packs->count()} packs");
                DB::table('packs')->insert($packs->toArray());
            });
    }

    private function trainings(): void
    {
        $this->title('Migrate products => trainings');

        $iterator = -1;

        DB::table('products')
            ->where('type', '=', 'training')
            ->selectRaw(implode(', ', [
                'name',
                'slug',
                'buyable',
                'CAST(price AS UNSIGNED) AS price',
                'visible',
                'created_at',
                'updated_at',
                "CONCAT('product-', id) AS mapping",
            ]))
            ->get()
            ->map(static function (stdClass $training) use (&$iterator) {
                $training->order = ++$iterator;

                return (array)$training;
            })
            ->tap(function (Collection $trainings) {
                $this->line("Found {$trainings->count()} trainings");
                DB::table('trainings')->insert($trainings->toArray());
            });
    }

    private function sections(): void
    {
        $this->title('Migrate products => sections');

        $trainingId = 0;
        $iterator   = -1;
        $mappings   = DB::table('trainings')
            ->get(['id', 'mapping'])
            ->mapWithKeys(static function (stdClass $training) {
                $oldId = (int)str_replace('product-', '', $training->mapping);

                return [$oldId => $training->id];
            })
            ->toArray();

        DB::table('products')
            ->join(DB::raw('products AS products2'), static function (JoinClause $join) {
                $join->on('products.parent_id', '=', 'products2.id')
                    ->where('products2.type', '=', 'training');
            })
            ->where('products.type', '=', 'section')
            ->selectRaw(implode(', ', [
                'products.name',
                'products.slug',
                'products.visible',
                'products.parent_id AS training_id',
                'products.created_at',
                'products.updated_at',
                "CONCAT('product-', products.id) AS mapping",
            ]))
            ->orderBy('products.parent_id')
            ->orderBy('products.order')
            ->orderBy('products.id')
            ->get()
            ->map(static function (stdClass $section) use ($mappings, &$trainingId, &$iterator) {
                $newId = $mappings[$section->training_id];

                if ($trainingId !== $newId) {
                    $trainingId = $newId;
                    $iterator   = -1;
                }

                $section->order       = ++$iterator;
                $section->training_id = $newId;

                return (array)$section;
            })
            ->tap(function (Collection $sections) {
                $this->line("Found {$sections->count()} sections");
                DB::table('sections')->insert($sections->toArray());
            });
    }

    private function lessons(): void
    {
        $this->title('Migrate products => lessons');

        $sectionId = 0;
        $iterator  = -1;
        $mappings  = DB::table('sections')
            ->get(['id', 'mapping'])
            ->mapWithKeys(static function (stdClass $section) {
                $oldId = (int)str_replace('product-', '', $section->mapping);

                return [$oldId => $section->id];
            })
            ->toArray();

        DB::table('products')
            ->join(DB::raw('products AS products2'), static function (JoinClause $join) {
                $join->on('products.parent_id', '=', 'products2.id')
                    ->where('products2.type', '=', 'section');
            })
            ->where('products.type', '=', 'lesson')
            ->join('modules', 'products.module_id', '=', 'modules.id')
            ->selectRaw(implode(', ', [
                'products.name AS name',
                'products.slug AS slug',
                "JSON_EXTRACT(modules.content, '$.silicate') AS content",
                'products.visible AS visible',
                'products.parent_id AS section_id',
                'products.created_at AS created_at',
                'products.updated_at AS updated_at',
                "CONCAT('product-', products.id) AS mapping",
            ]))
            ->orderBy('products.parent_id')
            ->orderBy('products.order')
            ->orderBy('products.id')
            ->get()
            ->map(static function (stdClass $lesson) use ($mappings, &$sectionId, &$iterator) {
                $newId = $mappings[$lesson->section_id];

                if ($sectionId !== $newId) {
                    $sectionId = $newId;
                    $iterator  = -1;
                }

                $lesson->order      = ++$iterator;
                $lesson->section_id = $newId;

                return (array)$lesson;
            })
            ->tap(function (Collection $lessons) {
                $this->line("Found {$lessons->count()} lessons");
                DB::table('lessons')->insert($lessons->toArray());
            });
    }
}
