<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

class CategoryObserver extends BaseObserver
{
    /**
     * @throws Http400BadRequestException
     */
    public function deleting(Category $category) : bool
    {
        $this->handleRelationDeletion($category, 'posts');

        return true;
    }
}
