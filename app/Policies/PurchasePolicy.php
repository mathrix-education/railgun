<?php

namespace App\Policies;

/**
 * Class PurchasePolicy.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.0.4
 */
class PurchasePolicy extends BasePolicy
{
    /**
     * @return bool
     */
    public function buy()
    {
        return true;
    }
}
