<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Railgun\Models\Traits\Acquerable;
use Railgun\Models\Traits\Orderable;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read int    $id
 * @property string      $name
 * @property string      $slug
 * @property bool        $buyable
 * @property int         $price
 * @property int         $order
 * @property bool        $visible
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * ---
 * @property-read Collection|Training[] $trainings
 */
class Pack extends Model
{
    use Acquerable;
    use Orderable;
    use Randomizable;

    protected $fillable = [
        'name',
        'slug',
        'buyable',
        'price',
        'order',
        'visible',
    ];

    protected $casts = [
        'buyable' => 'boolean',
        'visible' => 'boolean',
    ];

    /**
     * @return BelongsToMany<Training>
     */
    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class);
    }
}
