<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Randomizable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;
use function array_merge;

/**
 * @property-read int    $comment_id
 * @property-read int    $user_id
 * @property int         $value
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * ---
 * @property User        $user
 * @property Comment     $comment
 */
class Vote extends Model
{
    use Randomizable;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ['id', 'value', 'user_id', 'comment_id'];
    /** @var array Validation rules */
    protected $rules = [
        'user_id'    => 'required|exists:users,id',
        'comment_id' => 'required|exists:comments,id',
    ];

    public function getValidationRules(): array
    {
        return array_merge($this->rules, [
            'value' => ['required', Rule::in([1, -1])],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo User.
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
