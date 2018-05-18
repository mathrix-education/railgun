<?php

namespace App\Models;

use App\Models\Purchase;
use App\Models\Traits\Purchasable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Training
 *
 * @author Mathrix Back-End Team
 * @package App\Models
 * @since 1.0.1
 * @copyright Mathrix Education SA
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $price
 * @property bool $visible
 * @property int $order
 * @property string|null $created_at
 * @property string|null
 * @property-read Collection|Purchase[] $purchases
 * @property-read Collection|Section[] $sections
 * @property-read Collection|User[] $users
 * @method static Builder|Training whereId($value)
 * @method static Builder|Training whereName($value)
 * @method static Builder|Training whereSlug($value)
 * @method static Builder|Training whereDescription($value)
 * @method static Builder|Training wherePrice($value)
 * @method static Builder|Training whereVisible($value)
 * @method static Builder|Training whereOrder($value)
 * @method static Builder|Training whereCreatedAt($value)
 * @method static Builder|Training whereUpdatedAt($value)
 */
class Training extends BaseModel
{
    use Purchasable;

    /** @var array The guarded attributes */
    protected $guarded = [];

    /** @var array The validation rules on create */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "description" => "nullable",
    ];

    protected $casts = [
        "visible" => "boolean"
    ];


    /**
     * HasMany Section
     *
     * @return HasMany
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }


    /**
     * BelongsToMany User
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
