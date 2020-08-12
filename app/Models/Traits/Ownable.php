<?php

namespace Railgun\Models\Traits;

use Illuminate\Support\Facades\Auth;
use Railgun\Models\User;

/**
 * @property-read bool $owned If the model is owned by the current user.
 * @mixin \Railgun\Models\Contracts\Ownable
 */
trait Ownable
{
    public function getOwnedAttribute(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user && $user->owns($this);
    }
}
