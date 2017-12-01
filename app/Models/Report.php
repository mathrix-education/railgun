<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Report
 *
 * @property int $id
 * @property string $content
 * @property int $module_id
 * @property string $created_at
 * @property string $updated_at
 * @method static Builder|Report whereContent($value)
 * @method static Builder|Report whereCreatedAt($value)
 * @method static Builder|Report whereId($value)
 * @method static Builder|Report whereModuleId($value)
 * @method static Builder|Report whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $status
 * @property-read \App\Models\Module $module
 * @method static Builder|Report whereStatus($value)
 */
class Report extends BaseModel
{
    public const STATUSES = ["todo", "done", "validated"];

    /** @var array The fillable attributes */
    protected $fillable = ["id", "name", "content", "status", "module_id", "created_at", "updated_at"];

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;

    /** @var array The validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "visible" => "boolean"
    ];


    public function __construct(array $attributes = [])
    {
        // Dynamic rules
        $this->rules["status"] = "required|in:" . implode(",", self::STATUSES);

        parent::__construct($attributes);
    }


    /**
     * BelongsTo Module
     * @return BelongsTo
     */
    public function module() {
        return $this->belongsTo(Module::class);
    }
}