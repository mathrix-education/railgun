<?php

declare(strict_types=1);

namespace Railgun\Auth;

use Railgun\Models\Contracts\Ownable;
use Railgun\Models\User;

class OwnershipGate
{
    public const NAME = 'product';

    /**
     * @param User|null $user    The user.
     * @param Ownable   $product The product.
     *
     * @return bool If the user owns the product.
     */
    public function __invoke(?User $user, Ownable $product): bool
    {
        return $user !== null && $user->owns($product);
    }
}
