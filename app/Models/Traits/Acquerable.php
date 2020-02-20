<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Railgun\Models\Acquisition;
use Railgun\Models\User;

/**
 * @mixin HasRelationships
 */
trait Acquerable
{
    public function owners(): MorphToMany
    {
        return $this->morphToMany(User::class, 'product')
            ->as(Acquisition::class);
    }
}
