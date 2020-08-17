<?php

declare(strict_types=1);

namespace Railgun\Billing\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Railgun\Models\Acquisition;
use Railgun\Models\Chapter;
use Railgun\Models\Contracts\Ownable;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Lesson;
use Railgun\Models\Notion;
use Railgun\Models\Pack;
use Railgun\Models\Section;
use Railgun\Models\Subject;
use Railgun\Models\Training;
use Railgun\Models\Traits\Cacheable;
use function array_merge;
use function in_array;
use function resolveMorph;

/**
 * @mixin HasRelationships
 * @property-read Collection|Training[] $trainings
 */
trait OwnsProducts
{
    use Cacheable;

    /**
     * @return HasMany<Acquisition>
     */
    public function acquisitions(): HasMany
    {
        return $this->hasMany(Acquisition::class);
    }

    /**
     * @return MorphToMany<Chapter>
     */
    public function chapters(): MorphToMany
    {
        return $this->morphedByMany(Chapter::class, 'product', 'acquisitions')
            ->as(Acquisition::class);
    }

    /**
     * @return MorphToMany<Pack>
     */
    public function packs(): MorphToMany
    {
        return $this->morphedByMany(Pack::class, 'product', 'acquisitions')
            ->as(Acquisition::class);
    }

    /**
     * @return MorphToMany<Training>
     */
    public function trainings(): MorphToMany
    {
        return $this->morphedByMany(Training::class, 'product', 'acquisitions')
            ->as(Acquisition::class);
    }

    /**
     * @param Ownable $model The product to check.
     *
     * @return bool If the user owns the product.
     */
    public function owns(Ownable $model): bool
    {
        return in_array(
            $model->getGlobalKey(),
            $this->getCache()->remember('products_ids', 3600, fn() => $this->getOwnedProductGlobalIds()),
            true,
        );
    }

    /**
     * Get the products global ids owned by the user.
     *
     * @return string[] The product global ids as an unsorted array.
     */
    protected function getOwnedProductGlobalIds(): array
    {
        $products     = [];
        $acquisitions = Acquisition::query()
            ->where('user_id', '=', $this->id)
            ->get();

        // Chapters and below
        $chaptersIds = $acquisitions
            ->where('product_type', '=', resolveMorph(Chapter::class))
            ->pluck('product_id')
            ->toArray();

        Chapter::query()
            ->with([
                'notions.courses:id,notion_id',
                'notions.subjects.exercises:id,subject_id',
                'flashcards:id,chapter_id',
                'exams:id,chapter_id',
            ])
            ->whereIn('id', $chaptersIds)
            ->get()
            ->each(static function (Chapter $chapter) use (&$products): void {
                $products[] = $chapter->getGlobalKey();

                $chapter->notions->each(static function (Notion $notion) use (&$products): void {
                    $notion->courses->each(static function (Course $course) use (&$products): void {
                        $products[] = $course->getGlobalKey();
                    });

                    $notion->subjects->each(static function (Subject $subject) use (&$products): void {
                        $subject->exercises->each(static function (Exercise $exercise) use (&$products): void {
                            $products[] = $exercise->getGlobalKey();
                        });
                    });
                });

                $chapter->flashcards->each(static fn(Flashcard $flashcard) => $products[] = $flashcard->getGlobalKey());
                $chapter->exams->each(static fn(Exam $exam) => $products[] = $exam->getGlobalKey());
            });

        // Packs
        $products = array_merge(
            $products,
            $acquisitions->where('product_type', '=', resolveMorph(Pack::class))
                ->pluck('product_id')
                ->map(static fn(int $packId) => 'pack_' . $packId)
                ->toArray(),
        );

        // Trainings and below
        $trainingsIds = $acquisitions
            ->where('product_type', '=', resolveMorph(Training::class))
            ->pluck('product_id')
            ->toArray();

        Training::query()
            ->with([
                'sections:id,training_id',
                'sections.lessons:id,section_id',
            ])
            ->whereIn('id', $trainingsIds)
            ->get()
            ->each(static function (Training $training) use (&$products): void {
                $products[] = $training->getGlobalKey();

                $training->sections->each(static function (Section $section) use (&$products): void {
                    $products[] = $section->getGlobalKey();

                    $section->lessons->each(static function (Lesson $lesson) use (&$products): void {
                        $products[] = $lesson->getGlobalKey();
                    });
                });
            });

        return $products;
    }
}
