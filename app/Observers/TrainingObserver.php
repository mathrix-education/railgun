<?php
namespace App\Observers;

use App\Models\Section;
use App\Models\Training;
use App\Models\User;


/**
 * Class TrainingObserver
 *
 * @author Mathrix Back-End Team
 * @package App\Observers
 * @since 1.0.1
 * @copyright Mathrix Education SA
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

        // Link users
        if (isset($training->link["users"])) {
            $toSync = [];
            foreach ($training->link["users"] as $order => $userId) {
                if (User::whereId($userId)->exists()) {
                    $toSync[] = $userId;
                }
            }

            $training->users()->sync($toSync);
        }

        if (isset($training->link)) {
            unset($training->link);
        }

        return true;
    }


    /**
     * @param Training $training
     *
     * @return bool
     */
    public function deleting($training)
    {
        return Section::whereTrainingId($training)->delete();
    }
}
