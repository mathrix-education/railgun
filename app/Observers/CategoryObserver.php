<?php

namespace App\Observers;

use App\Models\Category;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class CategoryObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
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
        // Set the slug if needed
        if (empty($category->slug) ||
            ($category->isDirty("name") && !$category->isDirty("slug"))) {
            $category->slug = str_slug($category->name);
        }

        return true;
    }


    /**
     * @param Category $category
     *
     * @return bool
     * @throws Http400BadRequestException
     */
    public function deleting($category)
    {
        $this->handleRelationDeletion($category, "posts");

        return true;
    }
}
