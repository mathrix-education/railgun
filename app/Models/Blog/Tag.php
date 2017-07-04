<?php

namespace App\Models\Blog;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Blog\Tag
 * @property-read Collection|Post[] $posts
 * @mixin \Eloquent
 */
class Tag extends Model
{
    /**
     * BelongsToMany Post
     * @return BelongsToMany
     */
    public function posts()
    {
        return $this->belongsToMany('App\Models\Blog\Post');
    }
}