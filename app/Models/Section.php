<?php

declare(strict_types=1);

namespace Railgun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Orderable;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read  int    $id
 * @property-read  string $name
 * @property string       $slug
 * @property int          $order
 * @property bool         $visible
 * @property int          $training_id
 * @property-read string  $created_at
 * @property-read string  $updated_at
 */
class Section extends Model
{
    use Orderable;
    use Randomizable;

    protected $fillable = [
        'name',
        'slug',
        'order',
        'visible',
        'training_id',
    ];

    protected $orderGroups = ['training_id'];

    /**
     * @return BelongsTo<Training>
     */
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}
