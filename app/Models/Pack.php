<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Railgun\Billing\Stripe\Contracts\StripeSellable as StripeProductContract;
use Railgun\Billing\Stripe\Models\StripeSellable;
use Railgun\Models\Traits\Acquerable;
use Railgun\Models\Traits\Ownable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sellable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                   $id
 * @property      string                $name
 * @property      string                $slug
 * @property      bool                  $available
 * @property      int                   $price
 * @property      int                   $order
 * @property      bool                  $visible
 * @property-read Carbon                $created_at
 * @property-read Carbon                $updated_at
 * ---
 * @property-read Collection|Training[] $trainings
 */
class Pack extends BaseModel implements StripeProductContract
{
    use Acquerable;
    use Ownable;
    use Reorderable;
    use Sellable;
    use Sluggable;
    use StripeSellable;

    protected $fillable = [
        'name',
        'slug',
        'buyable',
        'price',
        'order',
        'visible',
    ];
    protected $casts    = [
        'available' => 'boolean',
        'visible'   => 'boolean',
    ];

    /**
     * @return BelongsToMany<Training>
     */
    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class);
    }
}
