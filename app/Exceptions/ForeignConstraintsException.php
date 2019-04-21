<?php

namespace App\Exceptions;

use Mathrix\Lumen\Bases\BaseModel;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
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
    protected const ERROR = "foreign_constraints";


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
