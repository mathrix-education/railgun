<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * Class Category
 *
 * @author Mathrix Back-End Team
 * @package App\Models
 * @since 1.0.0
 * @copyright Mathrix
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property-read Collection|Post[] $posts
 *
 * @method static Builder|Category whereImage($value)
 * @method static Builder|Category whereId($value)
 * @method static Builder|Category whereName($value)
 * @method static Builder|Category whereSlug($value)
 */
class Category extends BaseModel
{
    /** @var array The fillable attributes */
    protected $fillable = ["id", "name", "slug"];

    /** @var array Validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        //"image" => "required" // TODO: manage upload
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * HasMany Post
     *
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}