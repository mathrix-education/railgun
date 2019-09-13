<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraints;
use App\Models\Category;

class CategoryObserver extends BaseObserver
{
    /**
     * @param Category $category
     *
     * @return bool
     * @throws ForeignConstraints
     */
    public function deleting(Category $category) : bool
    {
        $this->handleRelationDeletion($category, 'posts');

        return true;
    }
}
