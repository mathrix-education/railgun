<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Report.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int $id
 * @property string $content
 * @property string $status
 * @property int $module_id
 * @property string $created_at
 * @property string $updated_at
 * @property Module $module
 */
class Report extends BaseModel
{
    public const STATUSES = ["todo", "done", "validated"];

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "type", "context", "content", "status", "module_id"];
    /** @var array The validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "context" => "max:255",
        "type" => "required",
        "content" => "required",
        "module_id" => "exists:modules,id",
        "visible" => "boolean",
        "slug" => "required|max:255",
    ];

    /** @var array Relation included by default */
    protected $with = ["module"];


    public function __construct(array $attributes = [])
    {
        // Dynamic rules
        $this->rules["status"] = "required|in:" . implode(",", self::STATUSES);

        parent::__construct($attributes);
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Module.
     *
     * @return BelongsTo
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
