<?php

declare(strict_types=1);

namespace Railgun\Auth;

use Railgun\Models\User;

class PremiumGate
{
    public const NAME = 'premium';

    /**
     * @param User|null $user The user.
     *
     * @return bool If the user is premium.
     */
    public function __invoke(?User $user): bool
    {
        return $user !== null && $user->premium;
    }
}
