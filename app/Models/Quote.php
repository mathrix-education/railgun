<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Quote
 *
 * @property int $id
 * @property string $content
 * @property string $author
 * @method static Builder|Quote whereId($value)
 * @method static Builder|Quote whereContent($value)
 * @method static Builder|Quote whereAuthor($value)
 * @mixin \Eloquent
 */
class Quote extends BaseModel
{
    /** @var array The fillable attributes */
    protected $fillable = ['content', 'author'];

    /** @var array Validation rules */
    protected $rules = [
        'content' => 'required',
        'author' => 'required|max:255'
    ];
}