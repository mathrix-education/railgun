<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Buster\Utils\SetOrder;
use stdClass;
use function implode;
use function str_replace;

/**
 * Migrate products => packs, trainings, sections, lessons
 */
class ProductsCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:products';

    public function handle(): void
    {
        $this->title('Migrating products => packs, trainings, sections, lessons');
        $this->packs();
        $this->trainings();
        $this->packTrainings();
        $this->sections();
        $this->lessons();
    }

    private function packs(): void
    {
        $this->line('Migrate products => packs');

        $iterator = -1;

        DB::table('products')
            ->where('type', '=', 'pack')
            ->selectRaw(implode(', ', [
                'name',
                'slug',
                'buyable AS available',
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
            ->tap(function (Collection $packs): void {
                $this->line("Found {$packs->count()} packs");
                DB::table('packs')->insert($packs->toArray());
            });
    }

    private function trainings(): void
    {
        $this->line('Migrate products => trainings');

        DB::table('products')
            ->where('type', '=', 'training')
            ->selectRaw(implode(', ', [
                'name',
                'slug',
                'buyable AS available',
                'CAST(price AS UNSIGNED) AS price',
                'visible',
                'created_at',
                'updated_at',
                "CONCAT('product-', id) AS mapping",
            ]))
            ->get()
            ->pipe(new SetOrder())
            ->map(static fn(stdClass $product) => (array)$product)
            ->tap(function (Collection $trainings): void {
                $this->line("Found {$trainings->count()} trainings");
                DB::table('trainings')->insert($trainings->toArray());
            });
    }

    private function packTrainings(): void
    {
        $this->line('Migrate product_product => pack_training');

        DB::table('product_product')
            ->join(DB::raw('products AS product_packs'), static function (JoinClause $join): void {
                $join->on('product_product.parent_id', '=', 'product_packs.id')
                    ->where('product_packs.type', '=', 'pack');
            })
            ->join('packs', DB::raw("CONCAT('product-', product_packs.id)"), '=', 'packs.mapping')
            ->join(DB::raw('products AS product_training'), static function (JoinClause $join): void {
                $join->on('product_product.child_id', '=', 'product_training.id')
                    ->where('product_training.type', '=', 'training');
            })
            ->join('trainings', DB::raw("CONCAT('product-', product_training.id)"), '=', 'trainings.mapping')
            ->selectRaw(implode(', ', [
                'packs.id AS pack_id',
                'trainings.id AS training_id',
            ]))
            ->orderBy('packs.id')
            ->orderBy('product_product.order')
            ->orderBy('product_product.id')
            ->get()
            ->pipe(new SetOrder('pack_id'))
            ->map(static fn(stdClass $product) => (array)$product)
            ->tap(function (Collection $data): void {
                $this->line("Found {$data->count()} associations");
                DB::table('pack_training')->insert($data->toArray());
            });
    }

    private function sections(): void
    {
        $this->line('Migrate products => sections');

        $trainingId = 0;
        $iterator   = -1;
        $mappings   = DB::table('trainings')
            ->get(['id', 'mapping'])
            ->mapWithKeys(static function (stdClass $training) {
                $oldId = (int)str_replace('product-', '', $training->mapping);

                return [$oldId => $training->id];
            })
            ->toArray();

        // Set type to sections
        DB::table('products')
            ->where('type', '=', 'training')
            ->select(['id'])
            ->get()
            ->pluck('id')
            ->tap(static function (Collection $trainingIds): void {
                DB::table('products')
                    ->whereIn('parent_id', $trainingIds->toArray())
                    ->update(['type' => 'section']);
            });

        DB::table('products')
            ->join(DB::raw('products AS products2'), static function (JoinClause $join): void {
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

                return $section;
            })
            ->map(static fn(stdClass $section) => (array)$section)
            ->tap(function (Collection $sections): void {
                $this->line("Found {$sections->count()} sections");
                DB::table('sections')->insert($sections->toArray());
            });
    }

    private function lessons(): void
    {
        $this->line('Migrate products => lessons');

        $sectionId = 0;
        $iterator  = -1;
        $mappings  = DB::table('sections')
            ->get(['id', 'mapping'])
            ->mapWithKeys(static function (stdClass $section) {
                $oldId = (int)str_replace('product-', '', $section->mapping);

                return [$oldId => $section->id];
            })
            ->toArray();

        // Set type to lesson
        DB::table('products')
            ->where('type', '=', 'section')
            ->select(['id'])
            ->get()
            ->pluck('id')
            ->tap(static function (Collection $trainingIds): void {
                DB::table('products')
                    ->whereIn('parent_id', $trainingIds->toArray())
                    ->update(['type' => 'lesson']);
            });

        DB::table('products')
            ->join(DB::raw('products AS products2'), static function (JoinClause $join): void {
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

                return $lesson;
            })
            ->map(static fn(stdClass $lesson) => (array)$lesson)
            ->tap(function (Collection $lessons): void {
                $this->line("Found {$lessons->count()} lessons");
                DB::table('lessons')->insert($lessons->toArray());
            });
    }
}
