<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Vote.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property int $value
 * @property int|null $user_id
 * @property int $comment_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * ---
 * @property User $user
 * @property Comment $comment
 */
class Vote extends BaseModel
{
    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "value", "user_id", "comment_id"];
    /** @var array Validation rules */
    protected $rules = [
        "user_id" => "required|exists:users,id",
        "comment_id" => "required|exists:comments,id"
    ];


    public function getValidationRules()
    {
        return array_merge($this->rules, [
            "value" => ["required", Rule::in([1, -1])]
        ]);
    }

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
     * BelongsTo Comment.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
