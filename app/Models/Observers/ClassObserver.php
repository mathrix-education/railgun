<?php
namespace App\Models\Observers;

use App\Models\Class_;


/**
 * Class UserObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class ClassObserver extends BaseObserver
{
    /**
     * @param Class_ $class
     *
     * @return bool
     */
    public function saving($class)
    {
        // Sluggify if needed
        if (
            empty($class->slug) ||
            ($class->isDirty("name") && !$class->isDirty("slug"))
        ) {
            $class->slug = str_slug($class->name);
        }

        // Sync disciplines
        if (!empty($class->link["disciplines"])) {
            $class->disciplines()->sync($class->link["disciplines"]);
        }

        if (!empty($class->link)) {
            unset($class->link);
        }

        return true;
    }
}
