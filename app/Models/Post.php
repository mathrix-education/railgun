<?php

namespace App\Models;

use App\Models\Interfaces\Commentable;
use App\Models\Traits\CommentableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Post.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int                  $id
 * @property string               $title
 * @property string               $slug
 * @property int                  $module_id
 * @property int                  $user_id
 * @property int                  $category_id
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Module               $module
 * @property Category             $category
 * @property User                 $user
 * @property Collection|Comment[] $comments
 */
class Post extends BaseModel implements Commentable
{
    use CommentableTrait;

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "title", "slug", "module_id", "category_id", "created_at", "updated_at"];
    /** @var array Validation rules */
    protected $rules = [
        "title" => "required|max:255",
        "slug" => "required|max:255",
        "content" => "required",
        "category_id" => "required|exists:categories,id",
    ];
    /** @var array The default included relations */
    protected $with = ["module"];

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

    /**
     * BelongsTo Category.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * BelongsTo User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
