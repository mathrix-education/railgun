<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Auth;
use Railgun\Models\Interaction;

/**
 * @property-read Collection|Interaction[] $interactions The model related {@see Interaction}.
 * @property-read Interaction|null         $interaction  The model related {@see Interaction} of teh current user.
 * @mixin HasRelationships
 */
trait Interactable
{
    /**
     * @return MorphToMany<Interaction>
     */
    public function interactions(): MorphToMany
    {
        return $this->morphToMany(Interaction::class, 'interactable');
    }

    /**
     * @return MorphOne<Interaction>
     */
    public function interaction(): MorphOne
    {
        $userId = Auth::id() ?? 0;

        return $this->morphOne(Interaction::class, 'interactable')
            ->where('user_id', '=', $userId);
    }
}
