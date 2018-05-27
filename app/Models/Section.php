<?php

namespace App\Models;

use App\Models\Traits\Loggable;
use App\Models\Traits\Purchasable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Section
 *
 * @package App\Models
 * @since 1.0.1
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $visible
 * @property int $order
 * @property int $training_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Collection|Lesson[] $lessons
 * @property-read Collection|Log[] $logs
 * @property-read Collection|Purchase[] $purchases
 * @property-read Training $training
 * @method static Builder|Section whereId($value)
 * @method static Builder|Section whereName($value)
 * @method static Builder|Section whereOrder($value)
 * @method static Builder|Section whereSlug($value)
 * @method static Builder|Section whereVisible($value)
 * @method static Builder|Section whereTrainingId($value)
 * @method static Builder|Section whereCreatedAt($value)
 * @method static Builder|Section whereUpdatedAt($value)
 */
class Section extends BaseModel
{
    use Purchasable, Loggable;

    /** @var array The guarded attributes */
    protected $fillable = ["id", "name", "slug", "visible", "order", "training_id"];
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
     * BelongsTo Training
     *
     * @return BelongsTo
     * @codeCoverageIgnore
     */
    public function training()
    {
        return $this->belongsTo(Training::class);
    }


    /**
     * HasMany Lesson
     *
     * @return HasMany
     * @codeCoverageIgnore
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
