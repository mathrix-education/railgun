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
        if (
            empty($class->name_short) ||
            ($class->isDirty("name") && !$class->isDirty("name_short"))
        ) {
            $class->name_short = $class->name;
        }
        if (empty($class->slug_short) ||
            ($class->isDirty("name_short") && !$class->isDirty("slug_short"))
        ) {
            $class->slug_short = str_slug($class->name_short);
        }

        // Sync disciplines
        if (!empty($class->link["disciplines"])) {
            $class->disciplines()->sync($class->link["disciplines"]);
            unset($class->link);
        }

        return true;
    }
}