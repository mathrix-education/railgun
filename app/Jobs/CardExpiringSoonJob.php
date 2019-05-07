<?php

namespace App\Jobs;

use Braintree\CreditCard;
use Braintree\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class CardExpiringSoonJob.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.1.0
 */
class CardExpiringSoonJob extends BaseJob
{
    /**
     * Get cards attached to a subscription expiring between to dates
     *
     * @param Carbon $start The starting date
     * @param Carbon $end The ending date
     *
     * @return Collection
     */
    public static function getCardsWithSubscriptionExpiringBetween(Carbon $start, Carbon $end)
    {
        $cards = CreditCard::expiringBetween($start->timestamp, $end->timestamp);
        $cardsWithSubscription = new Collection();

        /** @var CreditCard $card */
        foreach ($cards as $card) {
            if (!empty($card->subscriptions)) {
                // Then, we need to filter cards which have no subscription attached

                /** @var Subscription $subscription */
                foreach ($card->subscriptions as $subscription) {
                    if ($subscription->status === Subscription::ACTIVE) {
                        $cardsWithSubscription->push($card);
                        break;
                    }
                }
            }
        }

        return $cardsWithSubscription;
    }


    public function handle()
    {
        // TODO: Implement handle() method.
    }
}
