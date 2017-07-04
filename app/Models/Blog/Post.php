<?php

namespace App\Models\Blog;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Blog\Post
 * @property-read \App\Models\Blog\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Blog\Tag[] $tags
 * @mixin \Eloquent
 */
class Post extends Model
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