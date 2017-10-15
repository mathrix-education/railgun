<?php

namespace App\Models\Observers;

use App\Models\Quote;

/**
 * Class QuoteObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class QuoteObserver extends BaseObserver
{
    /**
     * @param Quote $quote
     *
     * @return bool
     */
    public function saving($quote)
    {
        return parent::saving($quote);
    }
}