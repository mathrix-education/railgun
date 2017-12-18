<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Lesson
 *
 * @author Mathrix Back-End Team
 * @package App\Models
 * @since 1.0.1
 * @copyright Mathrix SA
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $image
 * @property int $section_id
 * @property int $module_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Module $module
 * @method static Builder|Lesson whereId($value)
 * @method static Builder|Lesson whereSlug($value)
 * @method static Builder|Lesson whereTitle($value)
 * @method static Builder|Lesson whereImage($value)
 * @method static Builder|Lesson whereSectionId($value)
 * @method static Builder|Lesson whereModuleId($value)
 * @method static Builder|Lesson whereCreatedAt($value)
 * @method static Builder|Lesson whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Lesson extends BaseModel
{
    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;

    /** @var array The fillable attributes */
    protected $fillable = ["title", "slug", "image", "section_id", "module_id", "created_at", "updated_at"];

    /** @var array Validation rules */
    protected $rules = [
        "title" => "required|max:255",
        "slug" => "required|max:255",
        "section_id" => "required|exists:sections,id",
        "module_id" => "required|exists:modules,id"
    ];


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
