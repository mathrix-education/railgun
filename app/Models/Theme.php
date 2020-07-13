<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read int                  $id
 * @property      string               $name
 * @property      int                  $order
 * @property      bool                 $visible
 * @property      int                  $discipline_id
 * @property      int                  $grade_id
 * @property-read Carbon               $created_at
 * @property-read Carbon               $updated_at
 * ---
 * @property-read Grade                $grade
 * @property-read Discipline           $discipline
 * @property-read Collection|Chapter[] $chapters
 */
class Theme extends Model
{
    use Randomizable;
    use Reorderable;

    protected       $fillable   = [
        'name',
        'order',
        'visible',
        'grade_id',
        'discipline_id',
    ];
    protected       $casts      = ['visible' => 'boolean'];
    protected array $orderGroup = ['discipline_id', 'grade_id'];

    /**
     * @return BelongsTo<Grade>
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * @return BelongsTo<Discipline>
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * @return HasMany<Chapter>
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('order');
    }
}
