<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Class_.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int                     $id
 * @property string                  $name
 * @property string                  $name_short
 * @property string                  $slug
 * @property string                  $slug_short
 * @property int                     $order
 * @property bool                    $visible
 * @property array                   $link
 * @property int|null                $parent_id
 * @property int                     $country_id
 * @property Country                 $country
 * @property Collection|Discipline[] $disciplines
 * @property Class_|null             $parent
 * @property Collection|User[]       $users
 *
 * @method static Builder|Class_ whereId($value)
 * @method static Builder|Class_ whereName($value)
 * @method static Builder|Class_ whereNameShort($value)
 * @method static Builder|Class_ whereSlug($value)
 * @method static Builder|Class_ whereSlugShort($value)
 * @method static Builder|Class_ whereOrder($value)
 * @method static Builder|Class_ whereVisible($value)
 * @method static Builder|Class_ whereParentId($value)
 * @method static Builder|Class_ whereCountryId($value)
 * @mixin \Eloquent
 */
class Class_ extends BaseModel
{
    /** @var string Custom table definition */
    protected $table = "classes";
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "slug", "order", "visible", "parent_id", "country_id", "link"];
    /** @var array Validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "order" => "required|integer",
        "visible" => "boolean",
        "country_id" => "required|exists:countries,id",
    ];

    /**
     * Get the cursus (ie. the class it self and all its previous).
     *
     * @return Collection
     */
    public function cursus()
    {
        $classes = new Collection();
        $classes->add($this);

        /** @var Class_ $end */
        $end = $classes->last();

        while (null !== $end->parent_id) {
            $classes[] = self::find($end->parent_id);
            $end = $classes->last();
        }

        return $classes;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Class_.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, "parent_id");
    }

    /**
     * BelongsTo Country.
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * BelongsToMany Discipline.
     *
     * @return BelongsToMany
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, "class_discipline", "class_id",
            "discipline_id");
    }

    /**
     * HasMany User.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
