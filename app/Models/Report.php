<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Report
 *
 * @package App\Models
 * @since 1.0.0
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
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
 * @property string $status
 * @property-read \App\Models\Module $module
 * @method static Builder|Report whereStatus($value)
 */
class Report extends BaseModel
{
    public const STATUSES = ["todo", "done", "validated"];


    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "content", "status", "module_id", "created_at", "updated_at"];
    /** @var array The validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "visible" => "boolean"
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
     * BelongsTo Module
     *
     * @return BelongsTo
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
