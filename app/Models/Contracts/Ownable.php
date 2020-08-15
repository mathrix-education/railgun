<?php

declare(strict_types=1);

namespace Railgun\Models\Contracts;

use Railgun\Models\BaseModel;

/**
 * @property-read bool $owned If the model is owned by the current user.
 * @mixin \Railgun\Models\Traits\Ownable
 * @mixin BaseModel
 */
interface Ownable
{
    public function getOwnedAttribute(): bool;
}
