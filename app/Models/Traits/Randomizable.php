<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Allow models to be randomly selected.
 *
 * @mixin Model
 */
trait Randomizable
{
    /**
     * Get a random instance of the model.
     *
     * @return static
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpDocSignatureInspection
     */
    public static function random()
    {
        return static::query()->inRandomOrder()->firstOrFail();
    }
}
