<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Quote.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int $id
 * @property string $key
 * @property string $text
 * @property int $module_id
 * ---
 * @property Module $module
 */
class Reference extends BaseModel
{
    protected $fillable = ["key", "text", "module_id"];
    protected $rules = [
        "key" => "required|max:255",
        "text" => "required",
        "module_id" => "required|exists:modules,id"
    ];
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Module.
     * @return BelongsTo
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
