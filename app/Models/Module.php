<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Module
 * @property int $id
 * @property string|null $name
 * @property string|null $type
 * @property array $questions
 * @property array $choices
 * @property array $solutions
 * @property array $corrections
 * @property-read Collection|Activity[] $activities
 * @method static Builder|Module whereChoices($value)
 * @method static Builder|Module whereCorrections($value)
 * @method static Builder|Module whereId($value)
 * @method static Builder|Module whereName($value)
 * @method static Builder|Module whereQuestions($value)
 * @method static Builder|Module whereSolutions($value)
 * @method static Builder|Module whereType($value)
 * @mixin \Eloquent
 */
class Module extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'questions' => 'array',
        'choices' => 'array',
        'solutions' => 'array',
        'corrections' => 'array',
    ];

    /**
     * BelongsToMany Activity
     * @return BelongsToMany
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Activity');
    }
}