<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Vote.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int $id
 * @property int $value
 * @property int|null $user_id
 * @property int $votable_id
 * @property string $votable_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User|null $user
 * @property Post|Comment $votable
 */
class Vote extends BaseModel
{
    public const VOTABLE_TYPES = ["comment"];

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "value", "user_id", "votable_id", "votable_type"];
    /** @var array Validation rules */
    protected $rules = [
        "user_id" => "required|exists:users,id",
        "votable_id" => "required|integer",
        "votable_type" => "required|in:comment",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * MorphTo.
     */
    public function votable(): MorphTo
    {
        return $this->morphTo();
    }
}
