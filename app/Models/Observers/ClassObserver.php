<?php
namespace App\Models\Observers;

use App\Models\Class_;
use Mathrix\Lumen\Observers\BaseObserver;

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
        // Ensure the name is present
        if (!$class->validate(['name' => 'required|max:255'])) {
            return false;
        }

        // Sluggify if needed
        if (
            empty($class->slug) ||
            ($class->isDirty('name') && !$class->isDirty('slug'))
        ) {
            $class->slug = str_slug($class->name);
        }
        if (
            empty($class->name_short) ||
            ($class->isDirty('name') && !$class->isDirty('name_short'))
        ) {
            $class->name_short = $class->name;
        }
        if (empty($class->slug_short) ||
            ($class->isDirty('name_short') && !$class->isDirty('slug_short'))
        ) {
            $class->slug_short = str_slug($class->name_short);
        }

        // Attach disciplines
        if (!empty($class->disciplines_ids)) {
            $class->disciplines()->attach($class->disciplines_ids);
            unset($class->disciplines_ids);
        }

        return parent::saving($class);
    }
}