<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Randomizable;

/**
 * @property int         $id
 * @property string      $title
 * @property string      $slug
 * @property array       $content
 * @property int         $order
 * @property bool        $visible
 * @property int         $category_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Post extends Model
{
    use Randomizable;

    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'order',
        'visible',
        'category_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Module.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * BelongsTo Category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * BelongsTo User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * HasMany Comments
     */
    public function comments(): HasMany
    {
        return $this->module->comments();
    }
}
