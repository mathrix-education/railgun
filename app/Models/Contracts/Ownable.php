<?php

declare(strict_types=1);

namespace Railgun\Models\Contracts;

use Railgun\Models\BaseModel;

/**
 * @mixin BaseModel
 */
interface Ownable
{
    public function getOwnedAttribute(): bool;
}
