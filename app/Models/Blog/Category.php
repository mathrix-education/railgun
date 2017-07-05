<?php
namespace App\Models\Blog;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Blog\Category
 *
 * @property-read Collection|Post[] $posts
 * @mixin \Eloquent
 */
class Category extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * HasMany Post
     * @return HasMany
     */
    public function posts()
    {
        return $this->hasMany('App\Models\Blog\Post');
    }
}