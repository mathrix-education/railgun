<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Railgun\Models\Traits\Randomizable;

/**
 * @property      int    $user_id
 * @property      string $interactable_type
 * @property      int    $interactable_id
 * @property      string $comprehension
 * @property      int    $score
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Interaction extends Model
{
    use Randomizable;

    public const COMPREHENSIONS = ['blur', 'clear'];

    protected $fillable = [
        'user_id',
        'interactable_type',
        'interactable_id',
        'comprehension',
        'score',
    ];

    public function interactable(): MorphTo
    {
        return $this->morphTo();
    }
}
