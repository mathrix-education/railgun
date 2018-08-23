<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class FamilyPack
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Models
 * @since 4.1.0
 * @copyright Mathrix Education SA
 * ----
 * @property int $id
 * @property int $child_id
 * @property int $parent_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $child
 * @property-read User $parent
 * @method static Builder|FamilyPack whereId($value)
 * @method static Builder|FamilyPack whereChildId($value)
 * @method static Builder|FamilyPack whereParentId($value)
 * @method static Builder|FamilyPack whereCreatedAt($value)
 * @method static Builder|FamilyPack whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FamilyPack extends BaseModel
{
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["child_id", "parent_id", "created_at", "updated_at"];


    /**
     * BelongsTo User
     *
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(User::class, "parent_id");
    }


    /**
     * BelongsTo User
     *
     * @return BelongsTo
     */
    public function child()
    {
        return $this->belongsTo(User::class, "child_id");
    }
}
