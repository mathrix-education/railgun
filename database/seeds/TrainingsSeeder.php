<?php

declare(strict_types=1);

use App\Models\Coupon;
use App\Models\Lesson;
use App\Models\Pack;
use App\Models\Section;
use App\Models\Training;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Seed the training data (packs, trainings, sections and lessons).
 *
 * Schema:
 * packs <=> trainings
 * └── sections
 *     └── lessons
 */
class TrainingsSeeder extends AdvancedSeeder
{
    private const PACK_COUNT            = 3;
    private const TRAINING_COUNT        = 10;
    private const SECTIONS_PER_TRAINING = [1, 2];
    private const LESSONS_PER_SECTION   = [1, 2];

    public function run(): void
    {
        // Packs
        $this->seedFactory(Pack::class, ['seed' => false, 'count' => self::PACK_COUNT])
            ->map(fn($pack, int $order) => ['order' => $order] + $pack)
            ->tap(fn() => $this->writeFactoryMessage(Pack::class))
            ->tap(fn(Collection $packs) => $this->seedCollection($packs, 'packs'));

        // Trainings
        $this->seedFactory(Training::class, self::TRAINING_COUNT);

        // Link packs <=> trainings
        $this->link(Pack::class, Training::class, ['callback' => 'order']);

        // Sections
        DB::table('trainings')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $trainingId) {
                return $this
                    ->seedFactory(
                        Section::class,
                        ['seed' => false, 'count' => $this->faker->numberBetween(...self::SECTIONS_PER_TRAINING)],
                        ['training_id' => $trainingId]
                    )
                    ->map(fn(array $section, int $order) => ['order' => $order] + $section);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Section::class))
            ->tap(fn(Collection $trainings) => $this->seedCollection($trainings, 'sections'));

        // Lessons
        DB::table('sections')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $sectionId) {
                return $this
                    ->seedFactory(
                        Lesson::class,
                        ['seed' => false, 'count' => $this->faker->numberBetween(...self::LESSONS_PER_SECTION)],
                        ['section_id' => $sectionId]
                    )
                    ->map(fn(array $section, int $order) => ['order' => $order] + $section);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Lesson::class))
            ->tap(fn(Collection $lessons) => $this->seedCollection($lessons, 'lessons'));

        // Coupons
        $this->seedFactory(Coupon::class, 15);

        // Link coupons <=> packs
        $this->link(Coupon::class, Pack::class, [
            'mode'     => 'left',
            'join'     => 'coupon_product',
            'rightFk'  => 'product_id',
            'callback' => fn($join) => ['product_type' => 'pack'] + $join,
        ]);

        // Link coupons <=> trainings
        $this->link(Coupon::class, Training::class, [
            'mode'     => 'left',
            'join'     => 'coupon_product',
            'rightFk'  => 'product_id',
            'callback' => fn($join) => ['product_type' => 'training'] + $join,
        ]);
    }
}
