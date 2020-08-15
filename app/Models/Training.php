<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Railgun\Billing\Stripe\Contracts\StripeSellable as StripeProductContract;
use Railgun\Billing\Stripe\Models\StripeSellable;
use Railgun\Models\Traits\Acquerable;
use Railgun\Models\Traits\Ownable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\Sellable;
use Railgun\Models\Traits\Sluggable;

/**
 * @property-read int                  $id
 * @property      string               $name
 * @property      string               $slug
 * @property      string|null          $image
 * @property      bool                 $available
 * @property      int                  $price
 * @property      int                  $order
 * @property      bool                 $visible
 * @property-read Carbon               $created_at
 * @property-read Carbon               $updated_at
 * ---
 * @property-read Collection|Pack[]    $packs
 * @property-read Collection|Section[] $sections
 * @property-read Collection|Grade[]   $grades
 */
class Training extends BaseModel implements StripeProductContract
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
        'duration',
        'content',
        'image',
        'available',
        'price',
        'order',
        'visible',
    ];
    protected $casts    = [
        'content'   => 'array',
        'available' => 'boolean',
        'visible'   => 'boolean',
    ];

    /**
     * @return BelongsToMany<Pack>
     */
    public function packs(): BelongsToMany
    {
        return $this->belongsToMany(Pack::class);
    }

    /**
     * @return HasMany<Section>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    /**
     * @return BelongsToMany<Grade>
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class);
    }
}
