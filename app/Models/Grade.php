<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Grade.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $order
 * @property bool $visible
 * @property array $link
 * @property int|null $parent_id
 * @property int $country_id
 * ---
 * @property Grade|null $parent
 * @property Country $country
 * @property Collection|Chapter[] $chapters
 * @property Collection|Discipline[] $disciplines
 * @property Collection|User[] $users
 */
class Grade extends BaseModel
{
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
     * Get the cursus (ie. the grade itself and all its previous).
     *
     * @return Collection
     */
    public function cursus()
    {
        $grades = new Collection();
        $grades->add($this);

        /** @var Grade $end */
        $end = $grades->last();

        while ($end->parent_id !== null) {
            $grades[] = self::query()->find($end->parent_id);
            $end = $grades->last();
        }

        return $grades;
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
     * BelongsTo Country.
     *
     * @return HasMany
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }


    /**
     * BelongsToMany Discipline.
     *
     * @return BelongsToMany
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class)
            ->withPivot("visible")
            ->orderBy("discipline_grade.order");
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
