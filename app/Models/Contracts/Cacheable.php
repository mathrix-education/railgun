<?php

declare(strict_types=1);

namespace Railgun\Models\Contracts;

use Illuminate\Cache\TaggedCache;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
interface Cacheable
{
    public function getCache(?string $store = null): TaggedCache;
}
