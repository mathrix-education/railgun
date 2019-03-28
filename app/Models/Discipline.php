<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Discipline.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $beta
 * @property array $link
 * ---
 * @property Collection|Grade[] $grades
 * @property Collection|Chapter[] $chapters
 */
class Discipline extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "slug", "beta", "visible", "link"];
    /** @var array Validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "beta" => "boolean"
    ];
    protected $appends = ["visible"];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsToMany Grade.
     *
     * @return BelongsToMany
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class);
    }


    /**
     * HasMany Chapter.
     *
     * @return HasMany
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
    /**
     * @return bool
     * @deprecated
     */
    public function getVisibleAttribute()
    {
        if (isset($this->pivot->attributes["visible"])) {
            return !empty((bool)$this->pivot->attributes["visible"]);
        } else {
            return null;
        }
    }
}
