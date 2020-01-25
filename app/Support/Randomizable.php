<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait Randomizable
{
    /**
     * @return static
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpDocSignatureInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function random()
    {
        return static::query()->inRandomOrder()->firstOrFail();
    }
}
