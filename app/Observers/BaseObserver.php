<?php

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * Class BaseObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
abstract class BaseObserver
{
    /**
     * Handle related models.
     *
     * @param BaseModel $model
     * @param string $relationName
     * @param bool $delete
     *
     * @throws ForeignConstraintsException
     */
    public function handleRelationDeletion($model, string $relationName, bool $delete = false): void
    {
        /** @var Collection $collection */
        $relation = $model->{$relationName}();

        // Handle HasMany relations
        if ($relation instanceof HasMany) {
            $collection = $relation->get();

            if ($collection->isEmpty()) {
                return;
            }

            if (!$delete) {
                throw new ForeignConstraintsException($model, $relationName);
            } else {
                $collection->each(function ($related) {
                    /** @var BaseModel $related */
                    $related->delete();
                });
            }
        }
    }
}
