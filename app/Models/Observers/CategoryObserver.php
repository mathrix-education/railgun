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
        // Sluggify if needed
        if (empty($category->slug) ||
            ($category->isDirty("name") && !$category->isDirty("slug"))) {
            $category->slug = str_slug($category->name);
        }

        return true;
    }

}