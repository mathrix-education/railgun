<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
 * @method static Builder|Section whereId($value)
 * @method static Builder|Section whereName($value)
 * @method static Builder|Section whereOrder($value)
 * @method static Builder|Section whereSlug($value)
 * @method static Builder|Section whereTrainingId($value)
 * @method static Builder|Section whereCreatedAt($value)
 * @method static Builder|Section whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Module[] $modules
 * @property-read \App\Models\Training $training
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
     * BelongsToMany Training
     * @return BelongsToMany
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class);
    }
}
