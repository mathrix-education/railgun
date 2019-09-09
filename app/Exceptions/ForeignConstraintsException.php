<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Models\BaseModel;
use Throwable;
use function class_basename;

class ForeignConstraintsException extends Http400BadRequestException
{
    public function __construct(BaseModel $model, string $relation, ?Throwable $previous = null)
    {
        $modelName = class_basename($model);

        parent::__construct([
            'model_id' => $model->id,
            'relation' => $relation,
        ], "$modelName id `{$model->id}` cannot be delete because it has related `$relation`", $previous);
    }
}
