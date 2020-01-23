<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http422UnprocessableEntity;
use Illuminate\Database\Eloquent\Model;
use Throwable;
use function class_basename;

/**
 * Thrown when trying to delete a model which still has foreign relations in the database.
 */
class ForeignConstraints extends Http422UnprocessableEntity
{
    public function __construct(BaseModel $model, string $relation, ?Throwable $previous = null)
    {
        $modelName = class_basename($model);

        parent::__construct(
            [
                'model_id' => $model->id,
                'relation' => $relation,
            ],
            "$modelName id `{$model->id}` cannot be delete because it has related `$relation`",
            $previous
        );
    }
}
