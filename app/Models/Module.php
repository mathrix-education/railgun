<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Module
 *
 * @property int $id
 * @property string|null $type
 * @property string|null $name
 * @property string|null $question
 * @property string|null $answer
 * @property string|null $correction
 * @property-read Collection|Activity[] $activities
 * @method static Builder|Module whereAnswer($value)
 * @method static Builder|Module whereCorrection($value)
 * @method static Builder|Module whereId($value)
 * @method static Builder|Module whereName($value)
 * @method static Builder|Module whereQuestion($value)
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
        'choices' => 'array'
    ];

    /**
     * BelongsToMany Activity
     * @return BelongsToMany
     */
    public function activities()
    {
        return $this->belongsToMany('App\Models\Activity');
    }
}