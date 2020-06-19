<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait SaveWithoutEvents
{

    /**
     * Save the model to the database without triggering any event.
     *
     * @param mixed[] $options The save options.
     *
     * @return bool If the save succeeded.
     */
    public function saveWithoutEvents(array $options = []): bool
    {
        return static::withoutEvents(fn() => $this->save($options));
    }
}
