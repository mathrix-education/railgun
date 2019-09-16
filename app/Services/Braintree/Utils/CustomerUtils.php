<?php

declare(strict_types=1);

namespace App\Services\Braintree\Utils;

use function str_replace;
use function uuid;

class CustomerUtils
{
    public static function generateId(): string
    {
        return 'cus_' . str_replace('-', '', uuid());
    }
}
