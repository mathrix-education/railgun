<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Notion.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $premium
 * @property bool $visible
 * @property string $type
 * ---
 * @property Collection|Chapter $chapter
 * @property Collection|Activity[] $activities
 * @property Collection|Comment[] $comments
 */
class Notion extends BaseModel
{
    public const TYPES = ["normal", "premium", "reminder", "further"];

    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        "id",
        "name",
        "slug",
        "type",
        "visible",
        "order",
        "chapter_id"
    ];
    /** @var array The validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "chapter_id" => "required|exists:chapters,id",
    ];
    protected $casts = [
        "visible" => "boolean"
    ];


    public function __construct(array $attributes = [])
    {
        // Dynamic rules
        $this->rules["type"] = "required|in:" . implode(",", self::TYPES);

        parent::__construct($attributes);
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * The notion parent chapter.
     *
     * @return BelongsTo
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }


    /**
     * The notion child activities.
     *
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderBy("order");
    }
}
