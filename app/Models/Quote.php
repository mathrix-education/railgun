<?php

namespace App\Models;

use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Quote.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Models
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
