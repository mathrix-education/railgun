<?php
namespace App\Models\Blog;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Blog\Tag
 *
 * @property-read Collection|Post[] $posts
 * @mixin \Eloquent
 */
class Tag extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * BelongsToMany Post
     * @return BelongsToMany
     */
    public function posts()
    {
        return $this->belongsToMany('App\Models\Blog\Post');
    }
}