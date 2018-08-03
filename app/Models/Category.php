<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * Model Category
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Models
 * @since 1.0.0
 * @copyright Mathrix Education SA
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property-read Collection|Post[] $posts
 * @method static Builder|Category whereImage($value)
 * @method static Builder|Category whereId($value)
 * @method static Builder|Category whereName($value)
 * @method static Builder|Category whereSlug($value)
 * @mixin \Eloquent
 */
class Category extends BaseModel
{
    /** @var array The attributes that are mass assignable */
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
     * @codeCoverageIgnore
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
