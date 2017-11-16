<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * App\Models\Discipline
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $beta
 * @property-read Collection|Chapter[] $chapters
 * @property-read Collection|Class_[] $classes
 * @method static Builder|Discipline whereId($value)
 * @method static Builder|Discipline whereName($value)
 * @method static Builder|Discipline whereSlug($value)
 * @method static Builder|Discipline whereBeta($value)
 * @method static Builder|Discipline whereVisible($value)
 * @property bool $visible
 * @mixin \Eloquent
 */
class Discipline extends BaseModel
{
    /** @var array The fillable attributes */
    protected $fillable = ["id", "name", "slug", "beta", "visible", "link"];

    /** @var array Validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "beta" => "boolean",
        "visible" => "boolean"
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * HasMany Chapter
     *
     * @return HasMany
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }


    /**
     * BelongsToMany Class_
     *
     * @return BelongsToMany
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Class_::class, "class_discipline", "discipline_id",
            "class_id");
    }
}