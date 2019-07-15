<?php

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Models\BaseModel;
use Throwable;

/**
 * Class ForeignConstraintsException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ForeignConstraintsException extends Http400BadRequestException
{
    /**
     * ForeignConstraintsException constructor.
     *
     * @param BaseModel $model
     * @param string $relation
     * @param Throwable|null $previous
     */
    public function __construct($model, string $relation, ?Throwable $previous = null)
    {
        $modelName = class_basename($model);

        parent::__construct([
            "model_id" => $model->id,
            "relation" => $relation
        ], "$modelName id `{$model->id}` cannot be delete because it has related `$relation`", $previous);
    }
}
