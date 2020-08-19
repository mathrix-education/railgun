<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Sluggable;

/**
 * A category is a group of related posts.
 *
 * @property-read int          $id         The category id.
 * @property      string       $name       The category name.
 * @property      string       $slug       The category slug (generated from Str::slug).
 * @property-read Carbon       $created_at The date/time when the category was created.
 * @property-read Carbon       $updated_at The date/time when the category was last updated.
 * ---
 * @property Collection|Post[] $posts      The posts related to the category.
 */
class Category extends BaseModel
{
    use Sluggable;

    protected $fillable = ['name', 'slug'];

    /**
     * @return HasMany<Post>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderBy('order');
    }
}
