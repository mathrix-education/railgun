<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraints;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Zero\Models\BaseModel;

abstract class BaseObserver
{
    /**
     * Handle related models.
     *
     * @throws ForeignConstraints
     */
    public function handleRelationDeletion(BaseModel $model, string $relationName, bool $delete = false) : void
    {
        /** @var Collection $collection */
        $relation = $model->{$relationName}();

        // Handle HasMany relations
        if (! ($relation instanceof HasMany)) {
            return;
        }

        $collection = $relation->get();

        if ($collection->isEmpty()) {
            return;
        }

        if (! $delete) {
            throw new ForeignConstraints($model, $relationName);
        }

        $collection->each(static function ($related) : void {
            /** @var BaseModel $related */
            $related->delete();
        });
    }
}
