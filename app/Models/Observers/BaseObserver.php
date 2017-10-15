<?php
namespace App\Models\Observers;

use App\Models\BaseModel;

/**
 * Class BaseObserver
 * @author Mathrix Team
 * @package App\Observers
 */
abstract class BaseObserver
{
    /**
     * Activate the validation on every save trial
     *
     * @param BaseModel $model
     *
     * @return bool|null
     */
    public function saving($model)
    {
        if ($model->validateOnSave() && !$model->validate()) {
            $model->setResponse($model->http422Validation([
                'message' => $model->errors()
            ]));

            return false;
        }

        return true;
    }
}
