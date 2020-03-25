<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Railgun\Models\Interaction;

/**
 * @property-read Interaction|null $interaction The current user interaction with the model.
 * @mixin HasRelationships
 */
trait Interactable
{
    /**
     * @return MorphOne
     */
    public function interaction(): MorphOne
    {
        $userId = Auth::id() ?? 0;

        return $this->morphOne(Interaction::class, 'interactable')
            ->where('user_id', '=', $userId);
    }
}
