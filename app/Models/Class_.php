<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * Model Class_
 *
 * @package App\Models
 * @since 1.0.0
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @property int $id
 * @property string $name
 * @property string $name_short
 * @property string $slug
 * @property string $slug_short
 * @property int $order
 * @property bool $visible
 * @property array $link
 * @property int|null $parent_id
 * @property int $country_id
 * @property-read Country $country
 * @property-read Collection|Discipline[] $disciplines
 * @property-read Class_|null $parent
 * @property-read Collection|User[] $users
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
    protected $fillable = ["id", "name", "slug", "order", "visible", "parent_id",
        "country_id", "link"];
    /** @var array Validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "order" => "required|integer",
        "visible" => "boolean",
        "country_id" => "required|exists:countries,id"
    ];


    /**
     * Get the cursus (ie. the class it self and all its previous)
     *
     * @return Collection
     */
    public function cursus()
    {
        $classes = new Collection();
        $classes->add($this);

        /** @var Class_ $end */
        $end = $classes->last();

        while (!is_null($end->parent_id)) {
            $classes[] = Class_::find($end->parent_id);
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
     * BelongsTo Class_
     *
     * @return BelongsTo
     * @codeCoverageIgnore
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Class_::class, "parent_id");
    }


    /**
     * BelongsTo Country
     *
     * @return BelongsTo
     * @codeCoverageIgnore
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }


    /**
     * BelongsToMany Discipline
     *
     * @return BelongsToMany
     * @codeCoverageIgnore
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, "class_discipline", "class_id",
            "discipline_id");
    }


    /**
     * HasMany User
     *
     * @return HasMany
     * @codeCoverageIgnore
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
