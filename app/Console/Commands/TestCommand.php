<?php

namespace App\Console\Commands;

use Braintree\CreditCard;
use Braintree\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class TestCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class TestCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "test";


    public function handle()
    {
        $currentMonth = Carbon::now()->month;

        $start = Carbon::create(2019, $currentMonth + 1, 1);
        $end = Carbon::create(2019, $currentMonth + 3, 1);

        $this->line("Fetching cards expiring between {$start->format("Y-m-d")} and {$end->format("Y-m-d")}");
        $cards = CreditCard::expiringBetween($start->timestamp, $end->timestamp);
        $cardsWithSubscription = new Collection();

        $this->line("Found {$cards->maximumCount()} cards.");
        $this->line("Analysing cards to find which are linked to subscriptions...");
        $this->progressStart($cards->maximumCount());
        /** @var CreditCard $card */
        foreach ($cards as $card) {
            if (!empty($card->subscriptions)) {
                /** @var Subscription $subscription */
                foreach ($card->subscriptions as $subscription) {
                    if ($subscription->status === Subscription::ACTIVE) {
                        $cardsWithSubscription->push($card);
                        break;
                    }
                }
            }

            $this->progressAdvance();
        }
        $this->progressFinish();

        $this->line("Found {$cardsWithSubscription->count()} cards with a linked active subscription");
    }
}
