<?php

namespace App\Models\Blog;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Blog\Category
 * @property-read Collection|Post[] $posts
 * @mixin \Eloquent
 */
class Category extends Model
{
    /**
     * HasMany Post
     * @return HasMany
     */
    public function posts()
    {
        return $this->hasMany('App\Models\Blog\Post');
    }
}