<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Support\Facades\Auth;
use Railgun\Models\Contracts\Ownable as OwnableContract;
use Railgun\Models\User;

/**
 * @mixin OwnableContract
 */
trait Ownable
{
    public function initializeOwnable(): void
    {
        $this->append('owned');
    }

    public function getOwnedAttribute(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        /** @noinspection PhpParamsInspection */
        return $user && $user->owns($this);
    }
}
