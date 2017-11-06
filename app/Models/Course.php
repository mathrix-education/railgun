<?php
namespace App\Models;

use App\Models\Traits\Commentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * App\Models\Course
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property int $category_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Category $category
 * @property-read Collection|Comment[] $comments
 * @method static Builder|Course whereId($value)
 * @method static Builder|Course whereSlug($value)
 * @method static Builder|Course whereTitle($value)
 * @method static Builder|Course whereContent($value)
 * @method static Builder|Course whereCategoryId($value)
 * @method static Builder|Course whereCreatedAt($value)
 * @method static Builder|Course whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Course extends BaseModel
{
    use Commentable;

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;

    /** @var array The fillable attributes */
    protected $fillable = ["title", "slug", "content", "category_id", "created_at", "updated_at"];

    /** @var array Validation rules */
    protected $rules = [
        "title" => "required|max:255",
        "slug" => "required|max:255",
        "content" => "required",
        "category_id" => "required|exists:categories,id"
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Category
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}