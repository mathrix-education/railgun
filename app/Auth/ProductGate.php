<?php

declare(strict_types=1);

namespace Railgun\Auth;

use Railgun\Models\Lesson;
use Railgun\Models\Pack;
use Railgun\Models\Section;
use Railgun\Models\Training;
use Railgun\Models\User;

class ProductGate
{
    public const NAME = 'product';

    /**
     * @param User|null                    $user    The user.
     * @param Pack|Training|Section|Lesson $product The product.
     *
     * @return bool If the user owns the product.
     */
    public function __invoke(?User $user, $product): bool
    {
        return $user !== null && $user->owns($product);
    }
}
