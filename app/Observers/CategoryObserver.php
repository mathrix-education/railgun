<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Str;
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
     * @throws Http400BadRequestException
     */
    public function deleting($category)
    {
        $this->handleRelationDeletion($category, "posts");

        return true;
    }
}
