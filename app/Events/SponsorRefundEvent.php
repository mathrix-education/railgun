<?php

namespace App\Events;

use App\Models\User;

/**
 * Class SponsorRefundEvent.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class SponsorRefundEvent extends BaseEvent
{
    public function __construct(User $customer, User $sponsor)
    {

    }
}
