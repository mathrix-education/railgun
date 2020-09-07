<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Railgun\Models\Contracts\Clonable;
use Railgun\Models\Contracts\Labelable as LabelableContract;
use Railgun\Models\Label;
use function array_diff;

/**
 * Allow models to have {@see Label}.
 *
 * @property-read Collection|Label[] $labels
 * @mixin HasRelationships
 */
trait Labelable
{
    public static function bootLabelable(): void
    {
        static::saved(static function (LabelableContract $model): void {
            if ($model instanceof Clonable) {
                $modelLabels = $model->labels->pluck('id')->sort()->toArray();

                $model->clones()
                    ->with('labels')
                    ->each(static function (LabelableContract $clone) use ($modelLabels): void {
                        $cloneLabels = $clone->labels->pluck('id')->sort()->toArray();

                        if (!array_diff($modelLabels, $cloneLabels)) {
                            return;
                        }

                        $clone->labels()->sync($modelLabels);
                    });
            }
        });
    }

    /**
     * @return MorphToMany<Label>
     */
    public function labels(): MorphToMany
    {
        return $this->morphToMany(Label::class, 'labelable');
    }
}
