<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\Braintree\Models\LineItem;

class LineItemObserver
{
    public function saving(LineItem $lineItem)
    {
        if ($lineItem->transaction->totally_refunded) {
            $lineItem->negated = true;
        }

        return true;
    }
}
