<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Railgun\Models\Interaction;

/**
 * @mixin HasRelationships
 */
trait Interactable
{
    /**
     * @return BelongsTo<Interaction>
     */
    public function interaction(): BelongsTo
    {
        $userId = Auth::id() ?? 0;

        return $this->belongsTo(Interaction::class)
            ->where('user_id', '=', $userId);
    }
}
