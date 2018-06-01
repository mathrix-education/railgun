<?php

namespace App\Models;

use App\Models\Interfaces\Purchasable;
use App\Models\Traits\Loggable;
use App\Models\Traits\PurchasableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Training
 *
 * @package App\Models
 * @since 1.0.1
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $price
 * @property bool $visible
 * @property int $order
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Collection|Log[] $logs
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
class Training extends BaseModel implements Purchasable
{
    use PurchasableTrait, Loggable;

    /** @var array The guarded attributes */
    protected $fillable = ["id", "name", "slug", "description", "price", "visible", "order", "created_at", "updated_at"];
    /** @var array The validation rules on create */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "description" => "nullable",
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "visible" => "boolean"
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * HasMany Section
     *
     * @return HasMany
     * @codeCoverageIgnore
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }


    /**
     * BelongsToMany User
     *
     * @return BelongsToMany
     * @codeCoverageIgnore
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
