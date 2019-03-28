<?php

namespace App\Services\Braintree\Models;

/**
 * Class Subscription.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class Subscription extends BraintreeModel
{
    /**
     * The findOr function, must be reimplemented.
     *
     * @param string $id
     * @param callable $callback
     *
     * @return mixed
     */
    public static function findOr(string $id, callable $callback)
    {
        \Braintree\Subscription::find($id);
    }
}
