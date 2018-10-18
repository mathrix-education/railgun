<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Discipline.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int                  $id
 * @property string               $name
 * @property string               $slug
 * @property bool                 $beta
 * @property array                $link
 * @property Collection|Chapter[] $chapters
 * @property Collection|Class_[]  $classes
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

    /**
     * @deprecated
     * @return bool
     */
    public function getVisibleAttribute()
    {
        if (isset($this->pivot->attributes["visible"])) {
            return !empty((bool) $this->pivot->attributes["visible"]);
        } else {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * HasMany Chapter.
     *
     * @return HasMany
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    /**
     * BelongsToMany Class_.
     *
     * @return BelongsToMany
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Class_::class, "class_discipline", "discipline_id",
            "class_id");
    }
}
