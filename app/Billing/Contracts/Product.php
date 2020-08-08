<?php

declare(strict_types=1);

namespace Railgun\Billing\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string|int $id
 * @property      string     $name
 * @property      int        $price     The product price in cents.
 * @property      bool       $available If the product is available.
 * @property      bool       $visible   If the product is visible.
 * @mixin Model
 */
interface Product
{
}
