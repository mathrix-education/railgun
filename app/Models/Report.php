<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

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
 */
class Report extends BaseModel
{
    public const STATUSES = ["todo", "done", "validated"];

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

}