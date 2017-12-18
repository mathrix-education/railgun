<?php

namespace App\Models;

use App\Models\Traits\Commentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;


/**
 * App\Models\Post
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int $module_id
 * @property int $user_id
 * @property int $category_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Module $module
 * @property-read Category $category
 * @property-read User $user
 * @property-read Collection|Comment[] $comments
 * @method static Builder|Post whereId($value)
 * @method static Builder|Post whereSlug($value)
 * @method static Builder|Post whereTitle($value)
 * @method static Builder|Post whereModuleId($value)
 * @method static Builder|Post whereCategoryId($value)
 * @method static Builder|Post whereUserId($value)
 * @method static Builder|Post whereCreatedAt($value)
 * @method static Builder|Post whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Post extends BaseModel
{
    use Commentable;

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;

    /** @var array The fillable attributes */
    protected $fillable = ["id", "title", "slug", "module_id", "category_id", "created_at", "updated_at"];

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
     * BelongsTo Module
     * @return BelongsTo
     */
    public function module() {
        return $this->belongsTo(Module::class);
    }

    /**
     * BelongsTo Category
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    /**
     * BelongsTo User
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
