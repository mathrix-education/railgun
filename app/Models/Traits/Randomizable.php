<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use function app;

/**
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
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpDocSignatureInspection
     */
    public static function random()
    {
        return static::query()->inRandomOrder()->firstOrFail();
    }
}
