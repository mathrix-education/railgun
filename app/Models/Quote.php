<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Model Quote.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int    $id
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
