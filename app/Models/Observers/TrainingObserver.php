<?php
namespace App\Models\Observers;

use App\Models\Section;
use App\Models\Training;


/**
 * Class TrainingObserver
 *
 * @author Mathrix Back-End Team
 * @package App\Models\Observers
 * @since 1.0.1
 * @copyright Mathrix
 */
class TrainingObserver extends BaseObserver
{
    /**
     * @param Training $training
     *
     * @return bool
     */
    public function saving($training)
    {
        // Sluggify if needed
        if (
            empty($training->slug) ||
            ($training->isDirty("name") && !$training->isDirty("slug"))
        ) {
            $training->slug = str_slug($training->name);
        }

        return true;
    }


    /**
     * @param Training $training
     *
     * @return bool
     */
    public function deleting($training) {
        return Section::whereTrainingId($training)->delete();
    }
}