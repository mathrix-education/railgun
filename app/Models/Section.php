<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Models\Contracts\Ownable as OwnableContract;
use Railgun\Models\Traits\Ownable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                 $id
 * @property      string              $name
 * @property      string              $slug
 * @property      int                 $order
 * @property      bool                $visible
 * @property      int                 $training_id
 * @property-read Carbon              $created_at
 * @property-read Carbon              $updated_at
 * ---
 * @property-read Training            $training
 * @property-read Collection|Lesson[] $lessons
 */
class Section extends BaseModel implements OwnableContract
{
    use Ownable;
    use Reorderable;
    use Sluggable;

    protected       $fillable   = [
        'name',
        'slug',
        'order',
        'visible',
        'training_id',
    ];
    protected array $orderGroup = ['training_id'];

    /**
     * @return BelongsTo<Training>
     */
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    /**
     * @return HasMany<Lesson>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
