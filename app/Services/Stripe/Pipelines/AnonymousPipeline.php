<?php

declare(strict_types=1);

namespace App\Services\Stripe\Pipelines;

use Laravel\Lumen\Routing\ProvidesConvenienceMethods;

/**
 * Hack to smash traits.
 */
abstract class AnonymousPipeline
{
    use ProvidesConvenienceMethods;
}
