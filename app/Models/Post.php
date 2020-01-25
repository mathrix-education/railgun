<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Randomizable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property array $content
 * @property int   $order
 * @property bool $visible
 * @property int $category_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Post extends Model
{
    use Randomizable;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ['id', 'title', 'slug', 'module_id', 'category_id', 'user_id'];
    /** @var array Validation rules */
    protected $rules = [
        'title' => 'required|max:255',
        'slug' => 'required|max:255',
        'category_id' => 'required|exists:categories,id',
    ];
    /** @var array The default included relations */
    protected $with = ['module'];

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
