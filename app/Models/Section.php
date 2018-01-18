<?php

namespace App\Models;

use App\Models\Lesson;
use App\Models\Training;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Section
 *
 * @author Mathrix Back-End Team
 * @package App\Models
 * @since 1.0.1
 * @copyright Mathrix SA
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $order
 * @property int $training_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Training $training
 * @property-read Collection|Lesson[] $lesson
 * @method static Builder|Section whereId($value)
 * @method static Builder|Section whereName($value)
 * @method static Builder|Section whereOrder($value)
 * @method static Builder|Section whereSlug($value)
 * @method static Builder|Section whereTrainingId($value)
 * @method static Builder|Section whereCreatedAt($value)
 * @method static Builder|Section whereUpdatedAt($value)
 */
class Section extends BaseModel
{
    /** @var array The guarded attributes */
    protected $guarded = [];


    /** @var array The validation rules on create */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "description" => "nullable",
    ];

    /**
     * BelongsTo Training
     * @return BelongsTo
     */
    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    /**
     * HasMany Lesson
     * @return HasMany
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
