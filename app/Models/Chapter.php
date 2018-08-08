<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


/**
 * Model Chapter
 *
 * @package App\Models
 * @since 1.0.0
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $order
 * @property bool $visible
 * @property int $class_id
 * @property int $discipline_id
 * @property array $link
 * @property-read Class_ $class_
 * @property-read Discipline $discipline
 * @property-read Collection|Notion[] $notions
 * @method static Builder|Chapter whereClassId($value)
 * @method static Builder|Chapter whereDisciplineId($value)
 * @method static Builder|Chapter whereId($value)
 * @method static Builder|Chapter whereName($value)
 * @method static Builder|Chapter whereSlug($value)
 * @method static Builder|Chapter whereOrder($value)
 * @method static Builder|Chapter whereVisible($value)
 * @property-read bool $fully_visible
 * @mixin \Eloquent
 */
class Chapter extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "name", "slug", "class_id", "discipline_id", "visible", "order", "link"];
    /** @var array Validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "class_id" => "required|exists:classes,id",
        "discipline_id" => "required|exists:disciplines,id",
        "visible" => "boolean"
    ];
    /** @var array Appends attributes */
    protected $appends = ["fully_visible"];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Class_
     *
     * @return BelongsTo
     * @codeCoverageIgnore
     */
    public function class_(): BelongsTo
    {
        return $this->belongsTo(Class_::class, "class_id");
    }


    /**
     * BelongsTo Discipline
     *
     * @return BelongsTo

     * @codeCoverageIgnore
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }


    /**
     * HasMany Notion
     *
     * @return BelongsToMany
     * @codeCoverageIgnore
     */
    public function notions(): BelongsToMany
    {
        return $this->belongsToMany(Notion::class)->withPivot(["order"]);
    }


    /**
     * fully_visible
     *
     * @return bool
     */
    public function getFullyVisibleAttribute(): bool
    {
        foreach ($this->notions as $notion) {
            if (!$notion->fully_visible) {
                return false;
            }
        }

        return true;
    }
}
