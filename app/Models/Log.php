<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Log
 *
 * @property int $id
 * @property string $event
 * @property int|null $user_id
 * @property string|null $value
 * @property string $created_at
 * @property string|null $loggable_type
 * @property int|null $loggable_id
 * @property-read User|null $user
 * @property-read Activity|Module $loggable
 * @method static Builder|Log whereCreatedAt($value)
 * @method static Builder|Log whereEvent($value)
 * @method static Builder|Log whereId($value)
 * @method static Builder|Log whereLoggableId($value)
 * @method static Builder|Log whereLoggableType($value)
 * @method static Builder|Log whereUserId($value)
 * @method static Builder|Log whereValue($value)
 * @mixin \Eloquent
 */
class Log extends BaseModel
{
    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;

    /** @var array */
    public $fillable = ['event', 'user_id', 'value', 'created_at', 'loggable_type', 'loggable_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'array'
    ];


    /**
     * Disable updated_at field update
     * @param mixed $value
     *
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        return $this;
    }


    /**
     * BelongsTo User
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * MorphTo
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}