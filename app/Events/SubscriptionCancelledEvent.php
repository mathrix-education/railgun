<?php

namespace App\Events;

use App\Services\Braintree\Utils\SubscriptionUtils;
use Braintree\Customer;
use Braintree\Subscription;
use Braintree\Transaction;

/**
 * Class SubscriptionCancelledEvent.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.2-rc1
 */
class SubscriptionCancelledEvent extends BaseEvent
{
    /** @var Subscription $subscription */
    public $subscription;
    /** @var Customer $customer */
    public $customer;


    /**
     * TransactionSuccessEvent constructor.
     *
     * @param Subscription $subscription
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
        $this->customer = SubscriptionUtils::getSubscriptionCustomer($this->subscription);
    }
}
