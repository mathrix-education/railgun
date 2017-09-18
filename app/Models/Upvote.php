<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mathrix\Lumen\Models\BaseModel;

class Upvote extends BaseModel
{

    /** @var array The fillable attributes*/
    protected $fillable = ['type', 'user_id', 'upvotable_id', 'upvotable_type'];

    /** @var array Validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255',
        'user_id' => 'required|exists:users,id'
    ];

    /**
     * BelongsTo User
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}