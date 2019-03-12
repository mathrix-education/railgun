<?php

namespace App\Observers;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Category;

/**
 * Class CategoryObserver.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
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


    /**
     * @param Category $category
     *
     * @throws Http400InvalidRequestException
     * @throws \Exception
     *
     * @return bool
     */
    public function deleting($category)
    {
        if (!$category->posts->isEmpty()) {
            throw new Http400InvalidRequestException("Cannot delete category {$category->id} be cause it has " .
                "attached posts");
        }

        return true;
    }
}
