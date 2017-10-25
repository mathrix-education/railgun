<?php

namespace App\Models\Observers;

use App\Models\Category;

/**
 * Class CategoryObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class CategoryObserver extends BaseObserver
{
    /**
     * @param Category $category
     *
     * @return bool
     */
    public function saving($category)
    {
        // Ensure the name is present
        if (!$category->validate(['name' => 'required|max:255'])) {
            return false;
        }

        // Sluggify if needed
        if (
            empty($category->slug) ||
            ($category->isDirty('name') && !$category->isDirty('slug'))
        ) {
            $category->slug = str_slug($category->name);
        }

        return true;
    }

}