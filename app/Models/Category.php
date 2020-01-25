<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Randomizable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int          $id
 * @property string            $name
 * @property string            $slug
 * @property-read Carbon       $created_at
 * @property-read Carbon       $updated_at
 * ---
 * @property Collection|Post[] $posts
 */
class Category extends Model
{
    use Randomizable;

    /** @var array The attributes that are mass assignable */
    protected $fillable = ['name', 'slug', 'image'];
    /** @var array Validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * HasMany Post.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
