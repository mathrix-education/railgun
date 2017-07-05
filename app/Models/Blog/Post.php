<?php
namespace App\Models\Blog;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Blog\Post
 * @property-read Category $category
 * @property-read Collection|Tag[] $tags
 * @mixin \Eloquent
 */
class Post extends BaseModel
{
    /**
     * BelongsTo Category
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Blog\Category');
    }

    /**
     * BelongsToMany Tag
     * @return BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('App\Models\Blog\Tag');
    }
}