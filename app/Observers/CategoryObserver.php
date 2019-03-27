<?php

namespace App\Observers;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Category;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

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
     * @return bool
     * @throws Http400BadRequestException
     */
    public function deleting($category)
    {
        if (!$category->posts->isEmpty()) {
            throw new Http400BadRequestException(
                ["category" => $category],
                "Cannot delete category {$category->id} since it has referenced posts."
            );
        }

        return true;
    }
}
