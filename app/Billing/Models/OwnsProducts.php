<?php

declare(strict_types=1);

namespace Railgun\Billing\Models;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Railgun\Models\Acquisition;
use Railgun\Models\Lesson;
use Railgun\Models\Pack;
use Railgun\Models\Section;
use Railgun\Models\Training;
use Railgun\Models\Traits\Cacheable;

/**
 * @mixin HasRelationships
 * @mixin Cacheable
 * @property-read \Illuminate\Database\Eloquent\Collection|Training[] $trainings
 */
trait OwnsProducts
{
    /**
     * @return HasMany<Acquisition>
     */
    public function acquisitions(): HasMany
    {
        return $this->hasMany(Acquisition::class);
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
     * @param Pack|Training|Section|Lesson $product The product to check.
     *
     * @return bool If the user owns the product.
     */
    public function owns($product): bool
    {
        if ($product instanceof Pack) {
            /** @var Collection $packIds */
            $packIds = $this->cache('packs', function () {
                return $this->packs()->get(['packs.id'])->pluck('id');
            });

            return $packIds->search($product->id) !== false;
        }

        if ($product instanceof Training) {
            /** @var Collection $trainingIds */
            $trainingIds = $this->cache('trainings', function () {
                return $this->trainings()->get(['trainings.id'])->pluck('id');
            });

            return $trainingIds->search($product->id) !== false;
        }

        if ($product instanceof Section) {
            $lessonIds = $this->cache('sections', function () {
                return $this->trainings()
                    ->with(['sections'])
                    ->get()
                    ->map(static function (Training $training) {
                        return $training->sections->map(static fn(Section $section) => $section->id);
                    })
                    ->flatten();
            });

            return $lessonIds->search($product->id) !== false;
        }

        if ($product instanceof Lesson) {
            $lessonIds = $this->cache('lessons', function () {
                return $this->trainings()
                    ->with(['sections.lessons'])
                    ->get()
                    ->map(static function (Training $training) {
                        return $training->sections->map(static function (Section $section) {
                            return $section->lessons->map(static fn(Lesson $lesson) => $lesson->id);
                        });
                    })
                    ->flatten();
            });

            return $lessonIds->search($product->id) !== false;
        }

        return false; // @codeCoverageIgnore
    }
}
