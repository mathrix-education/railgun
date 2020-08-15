<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Coupon;
use Railgun\Models\Lesson;
use Railgun\Models\Section;

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
    private const SECTIONS_PER_TRAINING = [1, 2];
    private const LESSONS_PER_SECTION   = [1, 2];

    /**
     * @throws JsonException
     */
    public function run(): void
    {
        $this->seedFile('packs.json');
        $this->seedFile('trainings.json');
        $this->seedFile('pack_training.json');
        $this->seedFile('grade_training.json');

        // Sections
        DB::table('trainings')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $trainingId) {
                return $this
                    ->seedFactory(
                        Section::class,
                        ['seed' => false, 'count' => $this->faker->numberBetween(...self::SECTIONS_PER_TRAINING)],
                        ['training_id' => $trainingId],
                    )
                    ->map(static fn(array $section, int $order) => ['order' => $order] + $section);
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
                        ['section_id' => $sectionId],
                    )
                    ->map(static fn(array $section, int $order) => ['order' => $order] + $section);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Lesson::class))
            ->tap(fn(Collection $lessons) => $this->seedCollection($lessons, 'lessons'));

        // Coupons
        $this->seedFactory(Coupon::class, 15);
    }
}
