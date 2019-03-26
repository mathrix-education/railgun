<?php

namespace App\Models;

use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Quote.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property string $content
 * @property string $author
 */
class Quote extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "content", "author"];
    /** @var array Validation rules */
    protected $rules = [
        "content" => "required",
        "author" => "required|max:255",
    ];
}
