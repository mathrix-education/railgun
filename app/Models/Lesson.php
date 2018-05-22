<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Lesson
 *
 * @package App\Models
 * @since 1.0.0
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $image
 * @property bool $visible
 * @property int $order
 * @property int $section_id
 * @property int $module_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Module $module
 * @method static Builder|Lesson whereId($value)
 * @method static Builder|Lesson whereSlug($value)
 * @method static Builder|Lesson whereTitle($value)
 * @method static Builder|Lesson whereImage($value)
 * @method static Builder|Lesson whereVisible($value)
 * @method static Builder|Lesson whereOrder($value)
 * @method static Builder|Lesson whereSectionId($value)
 * @method static Builder|Lesson whereModuleId($value)
 * @method static Builder|Lesson whereCreatedAt($value)
 * @method static Builder|Lesson whereUpdatedAt($value)
 */
class Lesson extends BaseModel
{
    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["title", "slug", "image", "visible", "order", "section_id", "module_id", "created_at",
        "updated_at"];
    /** @var array Validation rules */
    protected $rules = [
        "title" => "required|max:255",
        "slug" => "required|max:255",
        "section_id" => "required|exists:sections,id",
        "module_id" => "required|exists:modules,id"
    ];
    /** @var array The default included relations */
    protected $with = ["module"];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "visible" => "boolean"
    ];


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
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
