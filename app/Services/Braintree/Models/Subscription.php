<?php

namespace App\Services\Braintree\Models;

/**
 * Class Subscription.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Services\Braintree\Models
 */

/**
 * Class Subscription.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
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
